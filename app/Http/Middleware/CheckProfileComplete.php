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
     * Название: CheckProfileComplete -> handle
     * Дата-время: 21-12-2025 11:55
     * Описание: Проверка заполненности обязательных полей профиля.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Загружаем связанный профиль (отношение profile)
            $profile = $user->profile;

            // Обязательные поля
            $required = ['last_name', 'first_name', 'birth_date'];

            $isComplete = true;

            // Если профиля вообще нет в базе
            if (!$profile) {
                $isComplete = false;
            } else {
                // Если профиль есть, проверяем поля именно в НЕМ ($profile), а не в $user
                foreach ($required as $field) {
                    if (empty($profile->$field)) {
                        $isComplete = false;
                        break;
                    }
                }
            }

            // Если не заполнено и мы не на странице профиля — отправляем заполнять
            if (!$isComplete && !$request->is('profile*')) {
                return redirect()->route('profile.edit')->with('complete_required', true);
            }
        }

        return $next($request);
    }
}