<?php

/**
 * Название: RecoveryPassController
 * Дата-время: 06-01-2026 16:47
 * Описание: здесь я управляю восстановлением аккаунта, 
 * когда это необходимо, отправляю код на через MailService.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RecoveryPassController extends Controller
{
    public function showRecoveryPass() { 
        return view('auth.recovery_pass'); 
    }

    /**
     * ШАГ 1: Отправка кода
     */
    public function sendRecoveryCode(Request $request)
    {
        // Оставляем валидацию телефона в запросе (т.к. поле в форме обязательное)
        $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable',
        ]);

        $inputPhone = preg_replace('/[^0-9]/', '', $request->phone);
        $phoneSuffix = substr($inputPhone, -10);

        // Поиск пользователя только по Email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Пользователь с такими данными не найден.'])->withInput();
        }

        /**
         * ЛОГИКА ДЛЯ МИГРИРОВАВШИХ ПОЛЬЗОВАТЕЛЕЙ
         * Если телефон в базе есть — проверяем его. 
         * Если телефона в базе нет (пусто или null) — разрешаем сброс только по Email.
         */
        if (!empty($user->phone)) {
            $dbPhone = preg_replace('/[^0-9]/', '', $user->phone);
            
            if (substr($dbPhone, -10) !== $phoneSuffix) {
                \Log::warning("Ошибка восстановления. Телефон не совпал. Email: {$request->email}");
                return back()->withErrors(['email' => 'Пользователь с такими данными не найден.'])->withInput();
            }
        } else {
            \Log::info("Восстановление для пользователя без телефона (профиль пуст). Email: {$request->email}");
        }

        // --- Остальная логика без изменений ---
        $existing = DB::table('b2b_users_recovery')
            ->where('email', $user->email)
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            session(['recovery_email' => $user->email]);
            return redirect()->route('recovery.verify.form');
        }

        $code = (string)random_int(100000, 999999);

        DB::table('b2b_users_recovery')->updateOrInsert(
            ['email' => $user->email],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(15),
                'created_at' => now(),
            ]
        );

        try {
            $subject = "Код сброса пароля — Grifmaster B2B";
            $html = "<h3>Восстановление пароля</h3><p>Ваш код: <b>$code</b></p>";
            $sent = MailService::send($user->email, $subject, $html);
        } catch (\Exception $e) {
            Log::error("Recovery Mail Error: " . $e->getMessage());
            $sent = false;
        }

        if (!$sent) {
            DB::table('b2b_users_recovery')->where('email', $user->email)->delete();
            return back()->withErrors(['email' => 'Сбой отправки письма. Попробуйте позже.']);
        }

        session(['recovery_email' => $user->email]);
        return redirect()->route('recovery.verify.form');
    }

    /**
     * Аналогия с регистрацией: показ окна ввода кода
     */
    public function showVerifyForm()
    {
        $email = session('recovery_email');
        if (!$email) return redirect()->route('recovery.pass');

        // Проверяем, есть ли живой код в базе
        $exists = DB::table('b2b_users_recovery')
            ->where('email', $email)
            ->where('expires_at', '>', now())
            ->exists();

        if (!$exists) return redirect()->route('recovery.pass');

        return view('auth.recovery_verify', ['email' => $email]);
    }

    /**
     * ШАГ 2: Проверка кода и генерация пароля
     */
    public function verifyAndReset(Request $request)
    {
        // Пробуем взять email из скрытого поля формы, если нет - из сессии
        $email = $request->input('email', session('recovery_email'));
        
        if (!$email) {
            return redirect()->route('recovery.pass')->withErrors(['email' => 'Сессия истекла.']);
        }

        $request->validate([
            'code' => 'required|size:6',
            'email' => 'required|email' // Добавь в валидацию
        ]);

        $recovery = DB::table('b2b_users_recovery')
            ->where('email', $email)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$recovery) {
            return back()->withErrors(['code' => 'Неверный или просроченный код.']);
        }

        $newPassword = Str::random(10); 
        $user = User::where('email', $email)->first();
        
        $user->update(['password' => Hash::make($newPassword)]);
        DB::table('b2b_users_recovery')->where('email', $email)->delete();

        // Отправляем новый пароль
        $subject = "Ваш новый пароль — Grifmaster B2B";
        $html = "<h3>Кто-то (возможно вы) изменил ваш пароль в системе</h3><p>Новый пароль: <b>$newPassword</b></p><p>Если вы не меняли пароль, обратитесь к администратору!</p>";
        MailService::send($user->email, $subject, $html);

        session()->forget('recovery_email');

        return view('auth.success', [
            'title' => 'Пароль сброшен!',
            'message' => 'Новый пароль отправлен на вашу электронную почту.',
            'redirect_to' => route('login'),
            'delay' => 7
        ]);
    }
}