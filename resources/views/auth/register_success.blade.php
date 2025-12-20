<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Успешная регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <div class="auth-wrapper d-flex align-items-center justify-content-center">
        <div class="auth-card-glass text-center">
            <h1 class="display-4">🎉</h1>
            <h2 class="auth-title">Готово!</h2>
            <p class="auth-subtitle">{{ $ok_message }}</p>
            <hr class="border-white opacity-25">
            <p>Сейчас вы будете перенаправлены в личный кабинет...</p>
            <a href="{{ route('dashboard') }}" class="auth-btn-glass d-inline-block mt-3" style="text-decoration:none">Перейти в кабинет</a>
        </div>
    </div>
    <script>
        setTimeout(() => { window.location.href = "{{ route('dashboard') }}"; }, 3000);
    </script>
</body>
</html>