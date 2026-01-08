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
            
            {{-- ПРАВАЯ ЧАСТЬ ПУСТАЯ — это лечит 419 ошибку --}}
            <div class="topbar-right"></div>
        </div>
    </header>

    {{-- Тулбар возвращен на место --}}
    <div class="toolbar-wrapper">
        <div class="toolbar">
            @foreach($menu as $item)
                @if(in_array('toolbar', $item['show_in']))
                    <a href="{{ $item['url'] }}" class="toolbar-item">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['title'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>

    <div class="page-wrapper wide">
        <div class="page-card wide">
            @yield('content')
            @include('layouts.footer')
        </div>
    </div>

    @stack('scripts')
</body>
</html>