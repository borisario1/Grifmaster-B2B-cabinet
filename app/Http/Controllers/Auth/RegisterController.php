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

class RegisterController extends Controller
{
    public function showRegister() { return view('auth.register'); }

    /**
     * Обработка регистрации с учетом отказоустойчивости почты
     */
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:b2b_users,email',
            'phone' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $passwordHash = Hash::make($request->password);

        // 1. Проверяем глобальный конфиг (мгновенная регистрация)
        if (config('b2b.registration_direct')) {
            return $this->finalizeRegistration($request->email, $request->phone, $passwordHash, true);
        }

        // 2. Временная запись для верификации
        DB::table('b2b_users_temp')->updateOrInsert(
            ['email' => $request->email],
            [
                'phone' => $request->phone,
                'password_hash' => $passwordHash,
                'code' => $code,
                'created_at' => now(),
            ]
        );

        // 3. Попытка отправки письма
        try {
            $html = view('emails.register_confirm', ['code' => $code])->render();
            $sent = MailService::send($request->email, "Код подтверждения — Grifmaster B2B", $html);
        } catch (\Exception $e) {
            $sent = false; // Перехватываем ошибки соединения/VPN
        }

        // НОВЫЙ НЮАНС: Если письмо не ушло — создаем аккаунт сразу, но без отметки о верификации
        if (!$sent) {
            return $this->finalizeRegistration($request->email, $request->phone, $passwordHash, false);
        }

        session(['register_email' => $request->email]);
        return redirect()->route('register.verify');
    }

    /**
     * Финальный шаг верификации (ручной ввод кода)
     */
    public function verify(Request $request)
    {
        $email = session('register_email') ?? auth()->user()?->email;

        if (!$email) {
            return redirect()->route('login');
        }

        $tempUser = DB::table('b2b_users_temp')->where('email', $email)->first();

        if (!$tempUser || $tempUser->code !== $request->code) {
            return back()->withErrors(['code' => 'Неверный код подтверждения']);
        }

        // ТЕПЕРЬ: Если юзера нет, создаем его через finalizeRegistration
        $user = User::where('email', $tempUser->email)->first();
        
        if (!$user) {
            return $this->finalizeRegistration(
                $tempUser->email, 
                $tempUser->phone, 
                $tempUser->password_hash, 
                true
            );
        }

        // Если юзер уже был (отказоустойчивый путь), просто подтверждаем
        DB::transaction(function () use ($tempUser, $user) {
            $user->update(['email_verified_at' => now()]);
            DB::table('b2b_users_temp')->where('email', $tempUser->email)->delete();
        });

        return view('auth.success', [
            'title' => 'Почта подтверждена!',
            'message' => 'Спасибо! Ваш Email успешно подтвержден.',
            'redirect_to' => route('dashboard'),
            'delay' => 3
        ]);
    }

    /**
     * Метод "под ключ" для создания юзера
     * @param bool $isVerified - проставлять ли дату подтверждения
     */
    private function finalizeRegistration($email, $phone, $passwordHash, $isVerified = false)
    {
        $user = DB::transaction(function () use ($email, $phone, $passwordHash, $isVerified) {
            $user = User::create([
                'email' => $email,
                'phone' => $phone,
                'password' => $passwordHash,
                'role' => 'partner',
                'status' => 'active',
                'email_verified_at' => $isVerified ? now() : null, // Вот наш флаг
            ]);

            DB::table('b2b_users_temp')->where('email', $email)->delete();
            return $user;
        });

        auth()->login($user);

        $message = $isVerified 
            ? 'Ваш аккаунт успешно создан!' 
            : 'Аккаунт создан, но возникли проблемы с отправкой письма подтверждения. Вы можете начать работу, но позже подтвердите Email.';

        return view('auth.success', [
            'title' => 'Готово!',
            'message' => $message,
            'redirect_to' => route('profile.edit'),
            'delay' => 4
        ]);
    }
}