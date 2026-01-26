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
        // 1. Идентификация
        $identifier = Auth::check() ? Auth::id() : $request->ip();
        
        // --- [ЭТАП 1] ПРОВЕРКА ГЛОБАЛЬНОГО БАНА ---
        // Если этот ключ существует — пользователь в "штрафном боксе".
        // Мы отбиваем запрос СРАЗУ, даже не проверяя тайминги.
        $banKey = "ban:heavy:{$identifier}";
        
        if (Cache::has($banKey)) {
            return $this->response429($request, "
            Доступ ограничен для вас на 1-3 минуты. 
            Если вы будете продолжать осуществлять запросы так часто, доступ будет ограничен на более длинный период. 
            Если вы уверены, что это ошибка, обратитесь к администратору: 232@grifmaster.ru");
        }

        // --- [ЭТАП 2] ПРОВЕРКА КОНКРЕТНОГО ДЕЙСТВИЯ ---
        $actionKey = md5($request->route()->getName() ?? $request->path());
        $throttleKey = "throttle:{$identifier}:{$actionKey}";

        // Получаем лимит для этого действия (например, 0.8 сек или 5 сек)
        $delays = config('b2b.system.delays', []);
        $limit = $delays[$level] ?? config('b2b.system.heavy_action_delay', 15);

        $now = microtime(true);

        if (Cache::has($throttleKey)) {
            $lastHit = (float) Cache::get($throttleKey);
            $passed = $now - $lastHit;

            // Если прошло меньше положенного времени — ЭТО НАРУШЕНИЕ (429)
            if ($passed < $limit) {
                
                // --- [ЭТАП 3] ФИКСАЦИЯ НАРУШЕНИЯ ---
                // Неважно, какой был лимит. Факт нарушения есть.
                // Записываем страйк.
                $this->registerStrike($identifier, $banKey);
                // -----------------------------------

                $wait = number_format($limit - $passed, 1); 
                return $this->response429($request, "Ваши действия осуществляются слишком часто. Подождите {$wait} сек.");
            }
        }

        // Если всё чисто — обновляем таймер действия
        $ttl = (int) ceil($limit + 5); 
        Cache::put($throttleKey, $now, $ttl);

        return $next($request);
    }

    /**
     * Надежная регистрация нарушения
     */
    private function registerStrike($identifier, $banKey)
    {
        $strikeKey = "strikes:heavy:{$identifier}";
        
        // 1. Явно получаем текущее значение (или 0, если нет)
        $strikes = (int) Cache::get($strikeKey, 0);
        
        // 2. Увеличиваем
        $strikes++;

        // 3. Если набрал 3 нарушения — БАН
        if ($strikes >= 3) {
            // Ставим бан на 60 секунд
            Cache::put($banKey, true, 120);
            
            // Удаляем счетчик страйков
            Cache::forget($strikeKey);
        } else {
            // 4. Если бана нет, сохраняем счетчик с жизнью 10 секунд
            // (Обновляем таймер при каждом нарушении, чтобы ловить серии кликов)
            Cache::put($strikeKey, $strikes, 30);
        }
    }

    private function response429(Request $request, string $message)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false, 
                'message' => $message
            ], 429);
        }

        abort(429, $message);
    }
}