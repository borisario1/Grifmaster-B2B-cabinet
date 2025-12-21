{{-- 
    Название: register_success.blade.php
    Описание: Страница завершения регистрации. 
    Использует глобальный конфиг b2b.php для заголовков.
--}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Тянем название из конфига --}}
    <title>Успешная регистрация — {{ config('b2b.app_name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    
    {{-- Редирект через мета-тег (дублируем JS для надежности) --}}
    <meta http-equiv="refresh" content="3;url={{ route('dashboard') }}">
</head>
<body>

<div class="auth-wrapper d-flex align-items-center justify-content-center">
    <div class="auth-card-glass text-center">
        
        <div class="mb-3">
            <h1 class="display-4">🎉</h1>
        </div>

        <h2 class="auth-title">Готово!</h2>
        <p class="auth-subtitle">{{ $ok_message ?? 'Регистрация успешно завершена.' }}</p>
        
        <hr class="border-white opacity-25">
        
        <p class="small opacity-75">Сейчас вы будете автоматически перенаправлены в личный кабинет...</p>
        
        <a href="{{ route('dashboard') }}" class="auth-btn-glass d-inline-block mt-3" style="text-decoration:none">
            Перейти в кабинет
        </a>

        <div class="mt-4">
            <div class="spinner-border spinner-border-sm text-light" role="status"></div>
        </div>
    </div>
</div>

<script>
    // Редирект через 3 секунды
    setTimeout(() => { 
        window.location.href = "{{ route('dashboard') }}"; 
    }, 3000);
</script>

</body>
</html>