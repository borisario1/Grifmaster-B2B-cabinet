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

    <div class="toolbar-wrapper">
        <div class="toolbar">
            @foreach($menu as $item)
                @if(isset($item['show_in']) && in_array('toolbar', $item['show_in']))
                    @php
                        // Логика активного пункта (укороченная версия)
                        $currentPath = '/' . ltrim(request()->path(), '/');
                        $itemPath = '/' . ltrim(parse_url($item['url'], PHP_URL_PATH), '/');
                        $isActive = ($currentPath === $itemPath) || (str_starts_with($currentPath, $itemPath . '/'));
                        
                        // Твои исключения для склада/заказов
                        if ($itemPath === '/store' && (str_contains($currentPath, '/store/cart') || str_contains($currentPath, '/store/order'))) {
                            $isActive = false;
                        }
                    @endphp

                    <a href="{{ $item['url'] }}" 
                    class="toolbar-item {{ $isActive ? 'active' : '' }} {{ empty($item['title']) ? 'toolbar-icon-only' : '' }}">
                        
                        <i class="bi {{ $item['icon'] }}"></i>
                        
                        @if(!empty($item['title']))
                            <span>{{ $item['title'] }}</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </div>

    <div class="page-wrapper wide">
        <div class="page-card wide">
            @yield('content')
            @include('layouts.partials.footer')
        </div>
    </div>

    @stack('scripts')
</body>
</html>