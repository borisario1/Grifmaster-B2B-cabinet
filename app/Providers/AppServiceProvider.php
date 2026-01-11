<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Включаем логирование запросов только для отладки
        if (config('app.debug') && config('debugbar.debug_footer')) {
            \Illuminate\Support\Facades\DB::enableQueryLog();
        }
        
        // Если запущены тесты
        if (app()->runningUnitTests()) {
            
            // ПРОВЕРКА 1: Запрет на использование основной базы по имени
            if (config('database.connections.mysql.database') === 'b2b_grifmaster') {
                $this->stopTests("КРИТИЧЕСКАЯ ОШИБКА: Эти тесты приведут к затиранию основной базы 'b2b_grifmaster'! ОПЕРАЦИЯ ОТМЕНЕНА.");
            }

            // ПРОВЕРКА 2: Принуждение к использованию --env=testing
            $args = $_SERVER['argv'] ?? [];
            $hasTestingEnv = in_array('--env=testing', $args);

            if (!$hasTestingEnv) {
                $this->stopTests("ОШИБКА: Выполнение тестов разрешено только через команду 'php artisan test --env=testing'. ОПЕРАЦИЯ ОТМЕНЕНА.");
            }
            
            // ПРОВЕРКА 3: Если SQLite драйвер не подхватился и мы все еще на mysql
            if (config('database.default') === 'mysql') {
                $this->stopTests("ОСТАНОВКА: Драйвер SQLite не запустился или ошибка в env файле, тесты пытаются запуститься на MySQL. ОПЕРАЦИЯ ОТМЕНЕНА");
            }
        }

        // ГЛОБАЛЬНОЕ МЕНЮ
        View::composer('*', function ($view) {
            $view->with('menu', config('b2b_menu', []));
        });

        // Принудительно используем Bootstrap стили для пагинации
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Чтобы переменные $cartSummary и $unreadNotificationsCount 
        // были доступны во всех шаблонах без их ручной передачи из каждого контроллера
        view()->composer('layouts.app', function ($view) {
            // Безопасная инициализация сервиса
            $cartService = class_exists(\App\Services\CartService::class) ? app(\App\Services\CartService::class) : null;
            $isAuth = Auth::check();

            $view->with([
                'cartSummary' => ($isAuth && $cartService) ? $cartService->getSummary() : ['qty' => 0, 'pos' => 0, 'amount' => 0],
                
                // ВРЕМЕННО: ставим 0, пока не перенесем логику из Notifications.php в новую модель
                'unreadNotificationsCount' => 0, 
            ]);
        });
    }

    /**
     * Вспомогательный метод для гарантированной и чистой остановки
     */
    private function stopTests(string $message): void
    {
        // Пишем напрямую в поток вывода, чтобы текст не застрял в буфере
        fwrite(STDOUT, "\n\033[41m\033[37m " . $message . " \033[0m\n\n");

        die(1); 
    }
}
