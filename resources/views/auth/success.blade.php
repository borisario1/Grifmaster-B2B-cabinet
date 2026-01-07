{{-- 
    Название: auth/success.blade.php
    Описание: Универсальная страница успеха. 
    Использует глобальный конфиг b2b.php.
--}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Название страницы + системное имя из конфига --}}
    <title>{{ $title ?? 'Успешно' }} — {{ config('b2b.app_name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    @if (!empty($redirect_to))
        <meta http-equiv="refresh" content="{{ intval($delay ?? 3) }};url={{ $redirect_to }}">
    @endif
</head>

<body>

<div class="auth-wrapper d-flex flex-column align-items-center justify-content-center">

    <div class="auth-card-glass text-center">

        {{-- Логотип из конфига --}}
        <img src="{{ config('b2b.branding.fav_icon') }}"
             class="auth-logo-glass"
             alt="{{ config('b2b.app_name') }}">

        <h3 class="auth-title">{{ $title ?? 'Готово!' }}</h3>
        <p class="auth-subtitle">{{ $message ?? '' }}</p>

        <div class="mt-4">
            <div class="spinner-border text-light" role="status"></div>
        </div>

        @if (!empty($redirect_to))
            <p class="mt-4" style="font-size:13px; opacity:0.7;">
                Если перенаправление не произошло —
                <a href="{{ $redirect_to }}" class="auth-link" style="font-size:13px;">
                    нажмите здесь
                </a>.
            </p>
        @endif

    </div>

</div>

@if (!empty($redirect_to))
<script>
    // Дублируем редирект через JS для надежности
    setTimeout(() => { 
        window.location.href = "{{ $redirect_to }}"; 
    }, {{ intval($delay ?? 3) * 1000 }});
</script>
@endif

</body>
</html>