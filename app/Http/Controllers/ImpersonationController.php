<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ImpersonationController extends Controller
{
    /**
     * Войти по одноразовому токену (открывается в новом окне)
     */
    public function loginWithToken(string $token)
    {
        // Получаем данные из кэша
        $data = Cache::pull("impersonate_token:{$token}");

        if (!$data) {
            return response()->view('impersonate-error', [
                'message' => 'Токен недействителен или истёк. Попробуйте снова из панели управления.'
            ], 403);
        }

        $user = User::find($data['user_id']);
        
        if (!$user) {
            return response()->view('impersonate-error', [
                'message' => 'Пользователь не найден.'
            ], 404);
        }

        // Авторизуемся под выбранным пользователем в guard 'web'
        Auth::guard('web')->login($user);
        
        // Сохраняем ID админа для отображения баннера
        session(['impersonator_id' => $data['admin_id']]);

        return redirect('/dashboard');
    }

    /**
     * Завершить impersonation — показать страницу закрытия окна
     */
    public function stop()
    {
        // Выходим только из web guard (не затрагивает admin guard)
        Auth::guard('web')->logout();
        
        // Очищаем флаг impersonation
        session()->forget('impersonator_id');

        // Показываем страницу с инструкцией закрыть окно
        return response()->view('impersonate-close');
    }

    /**
     * Статический метод для генерации токена (вызывается из Filament Action)
     */
    public static function generateTokenForUser(User $user, int $adminId): string
    {
        // Генерируем одноразовый токен
        $token = Str::random(64);
        
        // Сохраняем в кэш на 3 минуты
        Cache::put("impersonate_token:{$token}", [
            'user_id' => $user->id,
            'admin_id' => $adminId,
        ], now()->addMinutes(3));

        return url("/impersonate-login/{$token}");
    }
}
