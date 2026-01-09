<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <header class="topbar">
        <div class="topbar-inner">
            <div class="topbar-left">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset(config('b2b.branding.logo_path')) }}" alt="{{ config('b2b.app_name') }}" class="topbar-logo">
                </a>
                <span class="topbar-title">{{ config('b2b.app_name') }}</span>
            </div>
            
            <div class="topbar-right">
                <a href="{{ route('cart.index') }}" 
                   class="topbar-icon {{ ($cartSummary['pos'] ?? 0) > 0 ? 'cart-not-empty' : '' }}" 
                   title="Корзина">
                    <i class="bi bi-basket3"></i>
                </a>

                <span class="topbar-cart-info">
                    @if(($cartSummary['pos'] ?? 0) > 0)
                        {{ $cartSummary['qty'] }} шт. / 
                        {{ $cartSummary['pos'] }} поз. / 
                        {{ number_format($cartSummary['amount'], 2, '.', ' ') }} ₽
                    @endif
                </span>

                <a href="{{ route('notifications.index') }}" class="topbar-icon topbar-notify" title="Уведомления">
                    <i class="bi bi-bell-fill"></i>
                    @if(($unreadNotificationsCount ?? 0) > 0)
                        <span class="notify-badge">{{ $unreadNotificationsCount }}</span>
                    @endif
                </a>

                <a href="{{ route('profile.edit') }}" class="topbar-icon" title="Мой профиль">
                    <i class="bi bi-person-circle"></i>
                </a>

                <a href="#" 
                   class="topbar-icon" 
                   title="Выйти"
                   onclick="event.preventDefault(); openModal('universalConfirm', () => { document.getElementById('logout-form').submit(); }, 'Выход из системы', 'Вы действительно хотите выйти?')">
                    <i class="bi bi-box-arrow-right"></i>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>

                <button class="topbar-burger" id="burgerBtn">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </header>

    <div class="toolbar-wrapper">
        <div class="toolbar">
            @foreach($menu as $item)
                @if(in_array('toolbar', $item['show_in']))
                    @php
                        $currentPath = '/' . ltrim(request()->path(), '/');
                        $itemUrl = '/' . ltrim($item['url'], '/');
                        $isActive = ($currentPath === $itemUrl) || (str_starts_with($currentPath, $itemUrl . '/'));
                        if ($itemUrl === '/partners-area/store' && str_contains($currentPath, '/store/cart')) { $isActive = false; }
                    @endphp
                    <a href="{{ $item['url'] }}" class="toolbar-item {{ $isActive ? 'active' : '' }}">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['title'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>

    <div class="page-wrapper {{ (isset($wideLayout) && $wideLayout) ? 'wide' : '' }}">
        <div class="page-card {{ (isset($wideLayout) && $wideLayout) ? 'wide' : '' }}">
            @yield('content')
            @include('layouts.footer')
        </div>
    </div>

    <div class="burger-overlay" id="burgerMenu">
        <div class="burger-inner">
            @foreach ($menu as $item)
                @if (in_array('burger', $item['show_in']))
                    <a href="{{ $item['url'] }}">
                        <i class="bi {{ $item['icon'] }}"></i>
                        {{ $item['title'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>

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
    document.addEventListener('DOMContentLoaded', function () {
        // Логика бургер-меню
        const btn  = document.getElementById('burgerBtn');
        const menu = document.getElementById('burgerMenu');
        if (btn && menu) {
            btn.addEventListener('click', (e) => { e.stopPropagation(); menu.classList.toggle('open'); });
            document.addEventListener('click', (e) => { if (!menu.contains(e.target) && !btn.contains(e.target)) menu.classList.remove('open'); });
        }
    });

    // Глобальная функция показа тоста
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        // Настраиваем иконку в зависимости от типа
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