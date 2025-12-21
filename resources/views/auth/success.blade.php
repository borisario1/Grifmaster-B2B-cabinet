{{-- 
Название: auth/success.blade.php
Дата-время: 21-12-2025 19:10
Описание: Страница успеха
--}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Успешно' }} — Grifmaster B2B</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    @if (!empty($redirect_to))
        <meta http-equiv="refresh" content="{{ intval($delay ?? 3) }};url={{ $redirect_to }}">
    @endif
</head>

<body>

<div class="auth-wrapper d-flex flex-column align-items-center justify-content-center">

    <div class="auth-card-glass text-center">

        <img src="https://qr.grifmaster.ru/uploads/img/logo_circle.jpg"
             class="auth-logo-glass"
             alt="Grifmaster">

        <h3 class="auth-title">{{ $title ?? 'Готово!' }}</h3>
        <p class="auth-subtitle">{{ $message ?? '' }}</p>

        <div class="mt-4">
            <div class="spinner-border text-light" role="status"></div>
        </div>

        @if (!empty($redirect_to))
            <p class="mt-3" style="font-size:13px; opacity:0.7;">
                Если перенаправление не произошло —
                <a href="{{ $redirect_to }}" class="auth-link" style="font-size:13px;">
                    нажмите здесь
                </a>.
            </p>
        @endif

    </div>

</div>

</body>
</html>