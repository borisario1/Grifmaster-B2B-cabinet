{{-- 
    Название: verify.blade.php
    Дата-время: 21-12-2025 00:20
    Описание: Исправленная верстка ввода кода с использованием оригинальных CSS-классов.
--}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение — Grifmaster B2B</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<div class="auth-wrapper d-flex flex-column align-items-center justify-content-center">
    <div class="auth-card-glass text-center">
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

            {{-- Враппер и классы из auth.css --}}
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
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.code-input');
    const fullCodeInput = document.getElementById('fullCode');
    const form = document.getElementById('verifyForm');

    // Логика фокуса и ввода
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
                    // Переход на следующий инпут
                    if (e.target.value && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }

                    // Автоматический сабмит при заполнении всех 6 полей
                    const code = Array.from(inputs).map(i => i.value).join('');
                    if (code.length === 6) {
                        fullCodeInput.value = code;
                        form.submit(); // Отправляем форму автоматически
                    }
                });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });

    // Сборка кода перед отправкой
    form.addEventListener('submit', (e) => {
        let code = '';
        inputs.forEach(input => code += input.value);
        if(code.length < 6) {
            e.preventDefault();
            alert('Введите все 6 цифр');
            return;
        }
        fullCodeInput.value = code;
    });

    // Таймер
    let timeLeft = 60;
    const countdown = setInterval(() => {
        timeLeft--;
        document.getElementById('seconds').textContent = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(countdown);
            document.getElementById('timerContainer').classList.add('d-none');
            document.getElementById('resendLink').classList.remove('d-none');
        }
    }, 1000);
});

</script>

</body>
</html>