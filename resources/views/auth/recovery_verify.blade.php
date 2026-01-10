{{-- 
    Название: recovery_verify.blade.php
    Описание: Страница подтверждения сброса пароля. 
--}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение сброса пароля — {{ config('b2b.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<div class="auth-wrapper d-flex flex-column align-items-center justify-content-center">
    <div class="auth-card-glass text-center">
        <img src="{{ config('b2b.branding.fav_icon') }}" class="auth-logo-glass mb-3" alt="Logo">

        <h3 class="auth-title">Сброс пароля</h3>
        <p class="auth-subtitle">Код отправлен на указанный email</p>   
        <div class="alert alert-info py-2 small mb-4" style="background: rgba(255,255,255,0.1); border: none; color: #fff;">
            Код действителен 15 минут. Если письма нет — проверьте <b>Спам</b>. 
            Это единственный способ восстановить доступ.
        </div>

        {{-- ПРАВИЛЬНЫЙ ROUTE: recovery.verify.post --}}
        <form method="POST" action="{{ route('recovery.verify.post') }}" id="verifyForm">
            @csrf
            {{-- Передаем email в сессии или скрытым полем, если сессия может истечь --}}
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="code" id="fullCode">

            {{-- Используем твои стандартные классы для OTP --}}
            <div class="code-input-wrapper mb-4">
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="one-time-code" autofocus>
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="off">
                <input type="text" class="code-input" maxlength="1" pattern="\d*" autocomplete="off">
            </div>

            @if($errors->any())
                <div class="auth-error mb-3">{{ $errors->first() }}</div>
            @endif

            <button type="submit" class="auth-btn-glass w-100">Подтвердить и сбросить</button>
        </form>
    </div>

    @include('layouts.partials.auth-tech-footer')
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.code-input');
    const fullCodeInput = document.getElementById('fullCode');
    const form = document.getElementById('verifyForm');

    inputs.forEach((input, index) => {
        // Обработка ввода цифр
        input.addEventListener('input', (e) => {
            if (e.target.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            
            const code = Array.from(inputs).map(i => i.value).join('');
            if (code.length === 6) {
                fullCodeInput.value = code;
                form.submit(); // Автосабмит при заполнении всех полей
            }
        });

        // Обработка удаления (Backspace)
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });

        // Обработка вставки (Paste)
        input.addEventListener('paste', (e) => {
            const data = e.clipboardData.getData('text').trim();
            if (data.length === 6 && /^\d+$/.test(data)) {
                data.split('').forEach((char, i) => {
                    inputs[i].value = char;
                });
                fullCodeInput.value = data;
                form.submit();
            }
        });
    });
});
</script>
</body>
</html>

{{-- Сравнение с login.blade.php и verify.blade.php показало, что в recovery_verify.blade.php
    нужно использовать правильные маршруты и передавать email скрытым полем.
    Также добавлен JS для управления вводом кода подтверждения. --}}