<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SystemTest extends Command
{
    use \App\Traits\LoggableCommand;

    protected $signature = 'system:test {--log-id=}';
    protected $description = 'Запуск системных тестов и диагностика';

    public function handle()
    {
        $this->initLog();
        $this->logToDb("Запуск всех тестов приложения (php artisan test)...", 'info');

        // Используем Process для запуска тестов и перехвата вывода
        // --no-ansi чтобы убрать цветовые коды (они плохо читаются в простом логе)
        // Явно передаем переменные окружения, чтобы не задеть боевую БД
        $process = new \Symfony\Component\Process\Process(
            ['php', base_path('artisan'), 'test', '--env=testing', '--no-ansi'],
            base_path(),
            [
                'DB_DATABASE' => 'testing',
                'DB_CONNECTION' => 'mysql',
                'APP_ENV' => 'testing',
            ]
        );
        $process->setWorkingDirectory(base_path());
        
        // Увеличиваем таймаут, тесты могут идти долго
        $process->setTimeout(600); 
        $process->setIdleTimeout(60);

        try {
            $process->start();

            // Читаем вывод в реальном времени
            foreach ($process as $type => $data) {
                // $data может приходить кусками, но для простоты пишем как есть.
                // LoggableCommand::logToDb сама разберется с добавлением в output поле.
                // trim не делаем, чтобы сохранить форматирование строк (но уберем лишние пустые)
                if (trim($data)) {
                    $this->logToDb($data);
                }
            }

            // Ждем завершения
            $exitCode = $process->getExitCode();

            $this->logToDb("-----------------------------------");
            
            if ($exitCode === 0) {
                $this->logToDb("ВСЕ ТЕСТЫ ПРОШЛИ УСПЕШНО");
                $this->finishLog(true);
            } else {
                $this->logToDb("ОБНАРУЖЕНЫ ОШИБКИ В ТЕСТАХ", 'error');
                $this->finishLog(false, "Tests failed with exit code $exitCode");
            }

        } catch (\Exception $e) {
            $this->logToDb("Ошибка запуска тестов: " . $e->getMessage(), 'error');
            $this->finishLog(false, $e->getMessage());
        }
    }
}
