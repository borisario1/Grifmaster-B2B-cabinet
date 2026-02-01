<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync-all {--log-id= : ID лога запуска}';

    protected $description = 'Полная синхронизация: Импорт (1С) + Обогащение (API)';

    public function handle()
    {
        $logId = $this->option('log-id');
        $params = $logId ? ['--log-id' => $logId] : [];

        $this->info('Запуск этапа 1: Импорт товаров...');
        
        // Запускаем импорт
        $exitCode = $this->call('products:import', $params);

        // В текущей реализации ImportProducts перехватывает исключения,
        // поэтому exitCode может быть 0 даже при ошибке.
        // Но мы можем проверить лог, если нужно. 
        // Пока просто переходим к следующему шагу, так как обогащение 
        // не сломает ничего, если импорт прошел с ошибками (просто нечего будет обогащать).

        $this->info('Запуск этапа 2: Обогащение товаров...');
        $this->call('products:enrich', $params);
        
        return 0;
    }
}
