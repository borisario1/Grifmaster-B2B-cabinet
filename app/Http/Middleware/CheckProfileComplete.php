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
            
            // Благодаря withDefault() в модели User, здесь всегда будет объект, а не null
            $profile = $user->profile;

            // Обязательные поля для доступа к системе
            $required = ['last_name', 'first_name', 'birth_date'];
            $isComplete = true;

            foreach ($required as $field) {
                if (empty($profile->$field)) {
                    $isComplete = false;
                    break;
                }
            }

            // Если профиль "пустой" и пользователь пытается уйти с регистрации/профиля дальше в систему
            if (!$isComplete && !$request->is('profile*')) {
                return redirect()->route('profile.edit')
                    ->with('complete_required', 'Пожалуйста, завершите заполнение профиля.');
            }
        }

        return $next($request);
    }
}