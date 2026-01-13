{{-- 
    Название: login.blade.php
    Дата-время: 20-12-2025 21:10
    Описание: Шаблон страницы авторизации. Содержит форму входа, 
    вывод ошибок валидации и JS-эффект для ссылки регистрации.
--}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Grifmaster B2B</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Подключаем твой CSS из папки public/css/ --}}
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<div class="auth-wrapper d-flex flex-column align-items-center justify-content-center">

    <div class="auth-card-glass">

        <div class="text-center mb-4">
            <img src="https://qr.grifmaster.ru/uploads/img/logo_circle.jpg"
                 class="auth-logo-glass"
                 alt="Grifmaster">

            <h3 class="auth-title">Вход в систему</h3>
            <p class="auth-subtitle">Личный кабинет партнёра</p>
        </div>

        {{-- Вывод ошибок авторизации Laravel --}}
        @if ($errors->any())
            <div class="auth-error text-center mb-3">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf 

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control auth-input-glass" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label">Пароль</label>
                <input type="password" class="form-control auth-input-glass" name="password" required>
            </div>

            {{-- Блок "Запомнить меня" --}}
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" name="remember" id="remember" checked>
                <label class="form-check-label text-white" for="remember">
                    Запомнить меня
                </label>
            </div>

            <button type="submit" class="auth-btn-glass">Войти</button>
        </form>

        <div class="text-center mt-3">
            {{-- Используем именованный роут для регистрации --}}
            <a href="{{ route('register') }}" class="auth-link flash-text mx-2" id="flashText">
                Регистрация
            </a>
            <a href="{{ route('recovery.pass') }}" class="auth-link" id="flashText">
                Забыли пароль?
            </a>
        </div>

    </div>

@include('layouts.partials.auth-tech-footer')
    
</div>

<script>
{{-- Твой оригинальный JS для "мигающего" текста --}}
document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("flashText");
    let baseText = "Регистрация";
    let altText = "Стать богаче";

    function randomFlash() {
        let chance = Math.random();
        if (chance < 0.40) {
            el.textContent = altText;
            const duration = 300 + Math.random() * 600;
            setTimeout(() => {
                el.textContent = baseText;
            }, duration);
        }
    }

    function scheduleNext() {
        randomFlash();
        const next = 1500 + Math.random() * 2500;
        setTimeout(scheduleNext, next);
    }

    scheduleNext();
});
</script>

</body>
</html>