<div class="dashboard-footer">
    © 2005 - {{ date('Y') }} Grifmaster<br>
    Версия приложения: {{ config('b2b.version') }}, обновлено: {{ config('b2b.updated') }}<br>
    {{ config('b2b.support.name') }} <a href="mailto:{{ config('b2b.support.email') }}">{{ config('b2b.support.email') }}</a>

    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'manager']))
        <br>
        <a href="/management">Панель управления</a>
    @endif

    @if(config('app.debug') && config('debugbar.debug_footer'))
        @php
            $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
            $executionTime = round(microtime(true) - $startTime, 3);
            $queriesCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
            $memory = round(memory_get_usage() / 1024 / 1024, 2);
        @endphp

        <div class="debug-footer-info" style="background: #fffacd; border: 1px solid #ffd700; color: #d76e00; padding: 10px; font-family: monospace; font-size: 12px; text-align: center; margin-top: 10px;">
            [DEBUG MODE ON] 
            — Время: <strong>{{ $executionTime }} сек.</strong> 
            — БД Запросы: <strong>{{ $queriesCount }}</strong> 
            — Память: <strong>{{ $memory }} МБ</strong>
        </div>
    @endif
</div>