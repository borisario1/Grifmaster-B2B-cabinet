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
     * @param  string|null  $level  Уровень ограничения (min, short, medium, long)
     */
    public function handle(Request $request, Closure $next, string $level = null)
    {
        // 1. Если не авторизован - пропускаем
        if (!Auth::check()) {
            return $next($request);
        }

        $userId = Auth::id();
        
        // Ключ кэша
        $actionKey = md5($request->route()->getName() ?? $request->path());
        $cacheKey = "throttle:{$userId}:{$actionKey}";

        // 2. ПОЛУЧАЕМ ЛИМИТ (в секундах, мб дробным, например 0.8)
        $delays = config('b2b.system.delays', []);
        $limit = $delays[$level] ?? config('b2b.system.heavy_action_delay', 15);

        // 3. ПРОВЕРКА ЧЕРЕЗ MICROTIME
        // Текущее время с миллисекундами (float)
        $now = microtime(true);

        if (Cache::has($cacheKey)) {
            $lastHit = (float) Cache::get($cacheKey);
            
            // Сколько прошло времени с последнего клика
            $passed = $now - $lastHit;

            // Если прошло меньше лимита — БЛОКИРУЕМ
            if ($passed < $limit) {
                // Вычисляем, сколько осталось ждать (для красоты вывода)
                $wait = number_format($limit - $passed, 1); 
                $msg = "Слишком часто. Подождите {$wait} сек.";

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false, 
                        'message' => $msg
                    ], 429);
                }

                abort(429, $msg);
            }
        }

        // 4. ЗАПИСЬ В КЭШ
        // Мы кладем туда текущее время ($now).
        // Время жизни кэша (TTL) ставим чуть больше лимита (например, лимит + 2 сек),
        // чтобы запись сама исчезла, когда она станет неактуальной.
        // Округляем TTL до целого вверх, так как драйверы требуют int.
        $ttl = (int) ceil($limit + 5); 

        Cache::put($cacheKey, $now, $ttl);

        return $next($request);
    }
}