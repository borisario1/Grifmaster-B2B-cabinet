<?php

/**
 * Название: RegisterController
 * Дата-время: 20-12-2025 22:55
 * Описание: Управляет регистрацией: временное хранение данных, 
 * отправка кода через MailService и финальное создание аккаунта.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Показать форму регистрации
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Обработка данных формы и отправка кода
     */
    public function register(Request $request)
    {
        $request->validate([
        'email' => 'required|email|unique:b2b_users,email',
        'phone' => 'required',
        'password' => 'required|min:8|confirmed',
        ]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Очистка старых попыток этого email и запись в b2b_users_temp
        DB::table('b2b_users_temp')->where('email', $request->email)->delete();
        
        DB::table('b2b_users_temp')->insert([
            'email' => $request->email,
            'phone' => $request->phone,
            'password_hash' => Hash::make($request->password),
            'code' => $code,
            'created_at' => now(),
        ]);

        // Отправка через наш MailService
        $html = view('emails.register_confirm', ['code' => $code])->render();
        
        // Попытка отправки через внешний API
        $sent = MailService::send($request->email, "Код подтверждения — Grifmaster B2B", $html);

        if (!$sent) {
            // Если письмо не ушло, возвращаем ошибку, чтобы юзер не ждал зря
            return back()->withErrors(['email' => 'Ошибка отправки письма. Пожалуйста, обратитесь в поддержку.']);
        }
        // Сохраняем email в сессию, чтобы знать, кого проверять на странице verify
        session(['register_email' => $request->email]);

        return redirect()->route('register.verify');
    }

    /**
     * Страница ввода кода (твой verify.php)
     */
    public function showVerify()
    {
        if (!session('register_email')) return redirect()->route('register');
        return view('auth.verify', ['email' => session('register_email')]);
    }

    /**
     * Финальная проверка кода и создание юзера
     */
    public function verify(Request $request)
    {
        $email = session('register_email');
        $tempUser = DB::table('b2b_users_temp')->where('email', $email)->first();

        if (!$tempUser || $tempUser->code !== $request->code) {
            return back()->withErrors(['code' => 'Неверный код подтверждения']);
        }

        // Транзакция: создаем юзера и удаляем временные данные
        DB::transaction(function () use ($tempUser) {
            // В методе verify()
            $user = User::create([
                'email' => $tempUser->email,
                'phone' => $tempUser->phone,
                'password' => $tempUser->password_hash,
                'role' => 'partner',
                'status' => 'active',
            ]);
            DB::table('b2b_users_temp')->where('email', $tempUser->email)->delete();
            
            // Авторизуем пользователя
            auth()->login($user);
        });

        return view('auth.register_success', ['ok_message' => 'Ваш аккаунт успешно создан!']);
    }
}