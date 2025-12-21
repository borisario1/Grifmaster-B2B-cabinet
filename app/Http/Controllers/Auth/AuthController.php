<?php

/**
 * Название: AuthController (Контроллер управления доступом)
 * Дата-время: 20-12-2025 21:55
 * Описание: Отвечает за процессы входа пользователей в систему, 
 * выход из нее и отображение соответствующих форм.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Название: showLogin
     * Дата-время: 20-12-2025 21:55
     * Описание: Отображает страницу входа (шаблон login.blade.php).
     */
    public function showLogin() {
        return view('auth.login');
    }

    /**
     * Название: login
     * Дата-время: 20-12-2025 21:55
     * Описание: Валидирует входящие данные (email/пароль), 
     * проверяет их по базе данных и создает сессию при успехе.
     */
    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // На страницу успеха со спиннером
            return view('auth.success', [
                'title' => 'Авторизация успешна',
                'message' => 'Добро пожаловать! Загружаем данные...',
                'redirect_to' => route('dashboard'), // Спиннер перекинет сюда через 2 сек
                'delay' => 2
            ]);
        }

        return back()->withErrors(['email' => 'Неверный логин или пароль.'])->onlyInput('email');
    }

    /**
     * Название: logout
     * Дата-время: 20-12-2025 21:55
     * Описание: Завершает текущую сессию пользователя, удаляет данные из хранилища 
     * и обновляет CSRF-токен для предотвращения атак.
     */
    public function logout(Request $request) {
        // Выход из системы
        Auth::logout();
        
        // Очистка всех данных сессии
        $request->session()->invalidate();
        
        // Создание нового токена CSRF
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

}