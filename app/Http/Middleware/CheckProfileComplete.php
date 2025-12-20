<?php

/**
 * Название: CheckProfileComplete
 * Дата-время: 20-12-2025 22:40
 * Описание: Middleware для проверки заполненности профиля пользователя.
 * Если у пользователя нет записи в user_profiles, он перенаправляется на страницу дозаполнения данных.
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Если пользователь авторизован
        if (Auth::check()) {
            // Проверяем наличие связи с профилем (мы её сейчас добавим в модель User)
            // И исключаем бесконечный редирект, если пользователь уже на странице заполнения
            if (!Auth::user()->profile && !$request->is('profile/complete*')) {
                return redirect()->route('profile.complete');
            }
        }

        return $next($request);
    }
}
