<?php

/**
 * Название: ProfileController
 * Дата-время: 21-12-2025 12:55
 * Описание: Контроллер для управления профилем пользователя. 
 * Обрабатывает вывод формы, обновление данных и смену пароля.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Отображение страницы профиля
     */
    public function edit()
    {
        // Передаем текущего юзера в шаблон
        return view('auth.profile', ['user' => Auth::user()]);
    }

    /**
     * Сохранение основных данных профиля
     * Дата-время: 21-12-2025 14:20
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'last_name'  => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name'=> 'nullable|string|max:100',
            'birth_date' => 'required|date',
            'work_phone' => 'required|string|max:20',
            'messenger'  => 'nullable|string|max:100',
        ]);

        // updateOrCreate сам найдет профиль по user_id или создаст новый
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );
        // Показываем страницу успеха перед редиректом на дашборд
        return view('auth.success', [
            'title' => 'Профиль обновлен!',
            'message' => 'Ваши данные сохранены. Переходим в личный кабинет.',
            'redirect_to' => route('dashboard'),
            'delay' => 2
        ]);
    }

    /**
     * Смена пароля
     * Дата-время: 21-12-2025 14:20
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return view('auth.success', [
            'title' => 'Пароль изменен!',
            'message' => 'Безопасность вашего аккаунта обновлена.',
            'redirect_to' => route('profile.edit'),
            'delay' => 2
        ]);
    }

    /**
     * Функция изменения настроек профиля
     * Дата-время: 27-12-2025 20:18
     */
    public function updateNotification(Request $request)
    {
        // Валидация: разрешаем менять только конкретные поля
        $validated = $request->validate([
            'name' => 'required|in:notify_general,notify_news,notify_orders,notify_ticket',
            'value' => 'required|boolean', // Принимаем true/false или 1/0
        ]);

        // Получаем профиль текущего пользователя
        $profile = $request->user()->profile;

        // Обновляем конкретное поле (динамически)
        $profile->update([
            $validated['name'] => $validated['value']
        ]);

        return response()->json(['success' => true]);
    }
}