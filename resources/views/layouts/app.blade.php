<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('b2b.app_name'))</title>
    
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
    <link rel="stylesheet" href="{{ asset('css/requests.css') }}">
    @stack('styles')
</head>
<body>

    @include('layouts.partials.header')

    @include('layouts.partials.toolbar')

    <div class="page-wrapper {{ (isset($wideLayout) && $wideLayout) ? 'wide' : '' }}">
        <div class="page-card {{ (isset($wideLayout) && $wideLayout) ? 'wide' : '' }}">
            @yield('content')
            @include('layouts.partials.footer')
        </div>
    </div>

    @include('layouts.partials.burger')

    <div id="toast-container" class="toast-container"></div>

    <x-modal-confirm 
        id="universalConfirm" 
        title="Подтвердите действие" 
        icon="bi-exclamation-circle"
        btnClass="btn-primary"
    >
        Вы уверены, что хотите продолжить?
    </x-modal-confirm>

    <script>
        // Глобальная конфигурация
        window.B2B_CONFIG = {
            // Стандартная задержка
            defaultDelay: {{ config('b2b.system.heavy_action_delay', 15) }} * 1000,
            
            // Набор задержек (переводим в мс для JS)
            delays: {
                short:  {{ config('b2b.system.delays.short', 5) }} * 1000,
                middle:  {{ config('b2b.system.delays.middle', 15) }} * 1000,
                medium: {{ config('b2b.system.delays.medium', 30) }} * 1000,
                long:   {{ config('b2b.system.delays.long', 60) }} * 1000
            }
        };

        /**
         * 1. ЗАПУСК ДЕЙСТВИЯ (Клик по кнопке)
         * @param {HTMLElement} btn - Элемент кнопки
         * @param {string} loadingText - Текст во время ожидания
         * @param {number|null} customDelayMs - (Опционально) Своя задержка в мс
         * @returns {boolean}
         */
        function runHeavyAction(btn, loadingText = 'Подготовка...', customDelayMs = null) {
            if (btn.classList.contains('btn-blocked-wait')) return false;

            // Определяем время: если передали customDelayMs - берем его, иначе берем defaultDelay из конфига
            const delay = customDelayMs ? customDelayMs : (window.B2B_CONFIG.defaultDelay || 10000);
            const unlockTime = Date.now() + delay;

            // Сохраняем в память
            localStorage.setItem('heavy_action_unlock_time', unlockTime);

            // Запускаем визуал
            applyBlockState(btn, loadingText, unlockTime);

            return true; 
        }

        /**
         * 2. ПРОВЕРКА ПРИ ОТКРЫТИИ МОДАЛКИ (Восстановление состояния)
         * @param {string} btnId - ID кнопки для проверки
         */
        function checkGlobalCooldown(btnId) {
            const btn = document.getElementById(btnId);
            if (!btn) return;

            const unlockTime = parseInt(localStorage.getItem('heavy_action_unlock_time') || 0);
            const now = Date.now();

            if (unlockTime > now) {
                applyBlockState(btn, 'Ожидание...', unlockTime);
            }
        }

        /**
         * Вспомогательная функция визуальной блокировки с таймером
         */
        function applyBlockState(btn, text, unlockTime) {
            if (btn.classList.contains('btn-blocked-wait')) return;

            // 1. Сохраняем исходный вид
            if (!btn.getAttribute('data-original-html')) {
                btn.setAttribute('data-original-html', btn.innerHTML);
            }

            // 2. Фиксируем ширину
            const originalWidth = btn.offsetWidth;
            btn.classList.add('btn-blocked-wait');
            if (originalWidth > 0) btn.style.width = `${originalWidth}px`;

            // 3. ВСТАВЛЯЕМ HTML ОДИН РАЗ (Спиннер + span для текста)
            // Мы создаем отдельный span с классом .js-timer-text, чтобы менять только его
            const initialSeconds = Math.ceil((unlockTime - Date.now()) / 1000);
            btn.innerHTML = `
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span class="js-timer-text" style="margin-left: 6px;">${text} (${initialSeconds})</span>
            `;

            // 4. Запускаем таймер
            const interval = setInterval(() => {
                const remaining = unlockTime - Date.now();
                
                if (remaining <= 0) {
                    // ВРЕМЯ ВЫШЛО
                    clearInterval(interval);
                    btn.classList.remove('btn-blocked-wait');
                    btn.innerHTML = btn.getAttribute('data-original-html'); 
                    btn.style.width = '';
                    localStorage.removeItem('heavy_action_unlock_time'); 
                } else {
                    const seconds = Math.ceil(remaining / 1000);
                    const textSpan = btn.querySelector('.js-timer-text');
                    if (textSpan) {
                        textSpan.innerText = `${text} (${seconds})`;
                    }
                }
            }, 100); 
        }
    </script>
    <script>
    window.updateTopbarCart = function(summary) {
        const cartLink = document.getElementById('topbar-cart');
        const badgeContainer = document.getElementById('cart-qty-badge');
        const textContainer = document.getElementById('cart-text-info');

        if (!cartLink) return;

        if (summary && summary.pos > 0) {
            cartLink.classList.remove('cart-empty');
            cartLink.classList.add('cart-not-empty');
            if (badgeContainer) {
                badgeContainer.innerHTML = `<span class="cart-badge">${summary.qty}</span>`;
            }
            if (textContainer) {
                const amount = new Intl.NumberFormat('ru-RU').format(summary.amount);
                let word = 'позиций';
                const n = summary.pos % 100;
                const n1 = n % 10;
                if (n > 10 && n < 20) {
                    word = 'позиций';
                } else if (n1 > 1 && n1 < 5) {
                    word = 'позиции';
                } else if (n1 == 1) {
                    word = 'позиция';
                }
                textContainer.innerHTML = `
                    <span class="cart-total-amount">${amount}&nbsp;₽</span>
                    <span class="cart-total-label">${summary.pos} ${word}</span>
                `;
            }
        } else {
            cartLink.classList.remove('cart-not-empty');
            cartLink.classList.add('cart-empty');
            if (badgeContainer) badgeContainer.innerHTML = '';
            if (textContainer) textContainer.innerHTML = '';
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        const btn  = document.getElementById('burgerBtn');
        const menu = document.getElementById('burgerMenu');
        if (btn && menu) {
            btn.addEventListener('click', (e) => { e.stopPropagation(); menu.classList.toggle('open'); });
            document.addEventListener('click', (e) => { if (!menu.contains(e.target) && !btn.contains(e.target)) menu.classList.remove('open'); });
        }
    });

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        const isError = type === 'error' || type === 'danger';
        
        toast.className = `b2b-toast ${isError ? 'shake' : ''}`;
        const iconStyle = isError ? 'style="color:#e53e3e"' : '';
        
        toast.innerHTML = `<i class="bi ${icon}" ${iconStyle}></i> <span>${message}</span>`;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            toast.style.transition = '0.4s';
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }
    </script>

    @if(session('ok'))
        <script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('ok') }}", 'success'));</script>
    @endif

    @if(session('error'))
        <script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));</script>
    @endif

    @stack('scripts')

</body>
</html>