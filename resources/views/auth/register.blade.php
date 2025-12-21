{{-- 
    Название: register.blade.php
    Дата-время: 21-12-2025 19:45
    Описание: Страница первичной регистрации. 
    Данные брендинга тянутся из b2b.php.
--}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Используем системное название из конфига --}}
    <title>Регистрация — {{ config('b2b.app_name') }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<div class="auth-wrapper d-flex flex-column align-items-center justify-content-center">
    <div class="auth-card-glass">
        <div class="text-center mb-4">
            {{-- Ссылка на логотип из конфига --}}
            <img src="{{ config('b2b.branding.fav_icon') }}" class="auth-logo-glass" alt="{{ config('b2b.app_name') }}">
            
            <h3 class="auth-title">Регистрация</h3>
            <p class="auth-subtitle">Создание аккаунта партнёра</p>
        </div>

        @if ($errors->any())
            <div class="auth-error mb-3 text-center">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control auth-input-glass" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Телефон (+7 900 000-00-00)</label>
                <input type="text" class="form-control auth-input-glass" name="phone" id="phoneMask" value="{{ old('phone') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Пароль</label>
                <input type="password" class="form-control auth-input-glass" name="password" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Подтверждение пароля</label>
                <input type="password" class="form-control auth-input-glass" name="password_confirmation" required>
            </div>

            <button type="submit" class="auth-btn-glass">Зарегистрироваться</button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="auth-link">Уже есть аккаунт? Войти</a>
        </div>
    </div>
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