<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('b2b.app_name'))</title>
    
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
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

    {{-- 1. Глобальная конфигурация (PHP -> JS) --}}
    <script>
        window.B2B_CONFIG = {
            defaultDelay: {{ config('b2b.system.heavy_action_delay', 15) }} * 1000,
            delays: {
                short:  {{ config('b2b.system.delays.short', 5) }} * 1000,
                middle:  {{ config('b2b.system.delays.middle', 15) }} * 1000,
                medium: {{ config('b2b.system.delays.medium', 30) }} * 1000,
                long:   {{ config('b2b.system.delays.long', 60) }} * 1000
            }
        };
    </script>

    {{-- 2. Подключение основного ядра JS --}}
    <script src="{{ asset('js/app-core.js') }}"></script>

    {{-- 3. Вывод флеш-сообщений (сессия) --}}
    @if(session('ok'))
        <script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('ok') }}", 'success'));</script>
    @endif

    @if(session('error'))
        <script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));</script>
    @endif

    @stack('scripts')

</body>
</html>