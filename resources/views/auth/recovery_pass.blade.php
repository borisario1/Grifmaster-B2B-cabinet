{{-- 
    Название: recovery_pass.blade.php
    Дата-время: 07-01-2026 15:55
    Описание: Страница запроса на восстановление пароля (Шаг 1). 
    Здесь пользователь вводит Email и телефон для получения проверочного кода.
--}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля пользователя — {{ config('b2b.app_name') }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<div class="auth-wrapper d-flex flex-column align-items-center justify-content-center">
    <div class="auth-card-glass">
        <div class="text-center mb-4">
            {{-- Ссылка на логотип из конфига --}}
            <img src="{{ config('b2b.branding.fav_icon') }}" class="auth-logo-glass" alt="{{ config('b2b.app_name') }}">
            
            <h3 class="auth-title">Сбросить пароль</h3>
            <p class="auth-subtitle">Введите данные для получения кода восстановления</p>
        </div>

        @if ($errors->any())
            <div class="auth-error mb-3 text-center">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- ACTION изменен на recovery.send согласно web.php --}}
        <form method="POST" action="{{ route('recovery.send') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email (ваш логин)</label>
                <input type="email" class="form-control auth-input-glass" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Телефон (указанный при регистрации)</label>
                <input type="text" class="form-control auth-input-glass" name="phone" id="phoneMask" value="{{ old('phone') }}">
            </div>

            <button type="submit" class="auth-btn-glass w-100">Сбросить пароль</button>
        </form>

        <div class="text-center mt-3">
            Вспомнили пароль? <a href="{{ route('login') }}" class="auth-link">Войдите</a>
        </div>
    </div>

    @include('layouts.partials.auth-tech-footer')
</div>

{{-- Подключаем маску телефона --}}
<script src="https://unpkg.com/imask"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const phoneEl = document.getElementById('phoneMask');
        if (phoneEl) {
            IMask(phoneEl, { mask: '+{7} (000) 000-00-00' });
        }
    });
</script>

</body>
</html>