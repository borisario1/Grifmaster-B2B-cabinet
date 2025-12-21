{{-- 
Название: layouts/app.blade.php
Дата-время: 21-12-2025 12:10
Описание: Основной каркас (Layout) личного кабинета. Объединяет шапку, меню и футер.
--}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Партнёрская территория GRIFMASTER')</title>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
    @stack('styles') {{-- Для доп. стилей из контроллера --}}
</head>
<body>
    {{-- Сюда мы перенесем логику из твоего _topbar.php позже --}}
    <header class="topbar">
        <div class="topbar-inner">
            <div class="topbar-left">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('img/Logo_GRIFMASTER-03.png') }}" alt="Grifmaster" class="topbar-logo">
                </a>
                <span class="topbar-title">Партнёрская территория</span>
            </div>
            <div class="topbar-right">
                <a href="{{ route('profile.edit') }}" class="topbar-icon"><i class="bi bi-person-circle"></i></a>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="topbar-icon" style="background:none; border:none; color:white; cursor:pointer;">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <div class="page-card">
            @yield('content') {{-- Сюда будет вставляться контент страниц --}}
            
            <div class="dashboard-footer">
                © {{ date('Y') }} Grifmaster | Поддержка: {{ config('mail.from.address') }}
            </div>
        </div>
    </div>
</body>
</html>