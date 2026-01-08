{{-- 
    Название: verify.blade.php
    Описание: Страница подтверждения регистрации. 
--}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Динамический заголовок --}}
    <title>Подтверждение регистрации — {{ config('b2b.app_name') }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<div class="auth-wrapper d-flex flex-column align-items-center justify-content-center">
    <div class="auth-card-glass text-center">
        
        {{-- Логотип из конфига --}}
        <img src="{{ config('b2b.branding.fav_icon') }}" 
             class="auth-logo-glass mb-3" 
             alt="{{ config('b2b.app_name') }}">

        <h3 class="auth-title">Подтверждение почты</h3>
        <p class="auth-subtitle">Код отправлен на <strong>{{ $email }}</strong></p>

        @if ($errors->any())
            <div class="auth-error mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.verify.post') }}" id="verifyForm">
            @csrf
            <input type="hidden" name="code" id="fullCode">

            <div class="code-input-wrapper">
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="off" autofocus>
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="off">
            </div>

            <button type="submit" class="auth-btn-glass" id="submitBtn">Подтвердить</button>
        </form>

        <div class="mt-4">
            <p class="small opacity-75" id="timerContainer">
                Отправить повторно через <span id="seconds">60</span> сек.
            </p>
            <a href="#" id="resendLink" class="auth-link d-none">Отправить повторно</a>
        </div>
    </div>

@include('layouts.auth-tech-footer')

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.code-input');
    const fullCodeInput = document.getElementById('fullCode');
    const form = document.getElementById('verifyForm');

    // Авто-переход по инпутам
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }

            const code = Array.from(inputs).map(i => i.value).join('');
            if (code.length === 6) {
                fullCodeInput.value = code;
                form.submit();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });

    // Валидация перед ручным сабмитом
    form.addEventListener('submit', (e) => {
        let code = '';
        inputs.forEach(input => code += input.value);
        if(code.length < 6) {
            e.preventDefault();
            return;
        }
        fullCodeInput.value = code;
    });

    // Логика таймера
    let timeLeft = 60;
    const countdown = setInterval(() => {
        timeLeft--;
        const secEl = document.getElementById('seconds');
        if (secEl) secEl.textContent = timeLeft;
        
        if (timeLeft <= 0) {
            clearInterval(countdown);
            document.getElementById('timerContainer')?.classList.add('d-none');
            document.getElementById('resendLink')?.classList.remove('d-none');
        }
    }, 1000);
});
</script>

</body>
</html>