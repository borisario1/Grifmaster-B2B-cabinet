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
                {{-- Ссылка на профиль --}}
                <a href="{{ route('profile.edit') }}" class="topbar-icon" title="Мой профиль">
                    <i class="bi bi-person-circle"></i>
                </a>
                
                {{-- Кнопка выхода, стилизованная как иконка --}}
                <a href="#" 
                class="topbar-icon" 
                title="Выйти"
                onclick="event.preventDefault(); openModal('universalConfirm', () => { document.getElementById('logout-form').submit(); }, 'Выход из системы', 'Вы действительно хотите завершить текущую сессию и выйти из личного кабинета?')">
                    <i class="bi bi-box-arrow-right"></i>
                </a>

                {{-- Скрытая форма для отправки POST-запроса --}}
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>

                {{-- Кнопка бургера для мобилок --}}
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
                        // Более точная проверка на активность
                        $isActive = ($currentPath === $itemUrl) || (str_starts_with($currentPath, $itemUrl . '/'));
                        
                        if ($itemUrl === '/partners-area/store' && str_contains($currentPath, '/store/cart')) {
                            $isActive = false;
                        }
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
            
            {{-- футер --}}
            @include('layouts.footer')
        </div>
    </div>

    {{-- BURGER OVERLAY (как в оригинале) --}}
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

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn  = document.getElementById('burgerBtn');
        const menu = document.getElementById('burgerMenu');
        if (!btn || !menu) return;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                menu.classList.remove('open');
            }
        });
    });
    </script>

    @stack('scripts')

    {{-- Универсальное модальное окно (всегда под рукой) --}}
    <x-modal-confirm 
        id="universalConfirm" 
        title="Подтвердите действие" 
        icon="bi-exclamation-circle"
        btnClass="btn-primary"
    >
        Вы уверены, что хотите продолжить?
    </x-modal-confirm>
</body>
</html>