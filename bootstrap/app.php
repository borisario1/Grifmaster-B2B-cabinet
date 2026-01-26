<?php

/**
 * Название: bootstrap/app.php
 * Дата-время: 20-12-2025 23:25
 * Описание: Конфигурация жизненного цикла приложения. 
 * Здесь регистрируются маршруты и промежуточное ПО (Middleware).
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Регистрация алиасов Middleware
        $middleware->alias([
            'check.profile' => \App\Http\Middleware\CheckProfileComplete::class,
            'heavy.throttle' => \App\Http\Middleware\HeavyActionThrottle::class,
            'cache.response' => \App\Http\Middleware\CacheResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();