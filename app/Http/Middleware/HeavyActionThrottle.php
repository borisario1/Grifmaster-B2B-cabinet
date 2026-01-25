<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HeavyActionThrottle
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $level  Уровень ограничения (short, medium, long)
     */
    public function handle(Request $request, Closure $next, string $level = null)
    {
        // 1. Если не авторизован - пропускаем (или блокируем, зависит от задач)
        if (!Auth::check()) {
            return $next($request);
        }

        $userId = Auth::id();
        
        // Генерируем ключ, зависящий от названия роута (чтобы каталог не блокировал скачивание)
        $actionKey = md5($request->route()->getName() ?? $request->path());
        $cacheKey = "throttle:{$userId}:{$actionKey}";

        // 2. ПОЛУЧАЕМ СЕКУНДЫ ИЗ КОНФИГА (Server-Side Source of Truth)
        $delays = config('b2b.system.delays', []);
        
        // Если уровень передан (:long), берем его. Если нет - берем дефолтное (15)
        // Если переданного уровня нет в конфиге - берем дефолтное
        $seconds = $delays[$level] ?? config('b2b.system.heavy_action_delay', 15);

        // 3. ПРОВЕРКА
        if (Cache::has($cacheKey)) {
            // Формируем сообщение прямо здесь, на сервере
            $msg = "Мы ограничили частоту действий.\nПожалуйста, подождите {$seconds} сек.";

            // Если это AJAX (JS) - отдаем JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => $msg
                ], 429);
            }

            // Если это БРАУЗЕР - прерываем работу и показываем View 429
            // Передаем сообщение $msg внутрь исключения
            abort(429, $msg);
        }

        // 4. БЛОКИРОВКА (Записываем в кэш)
        Cache::put($cacheKey, true, now()->addSeconds($seconds));

        return $next($request);
    }
}