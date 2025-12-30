<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Импорт товаров: каждый будний день, каждые 2 часа с 8 до 18.
// Запускаем в 5-ю минуту часа, чтобы не грузить сервер в 00:00.
Schedule::command('products:import')
    ->weekdays()
    ->hourlyAt(5)
    ->between('08:00', '18:00')
    ->withoutOverlapping()
    ->onOneServer();