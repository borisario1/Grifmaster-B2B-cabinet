@extends('layouts.error')

{{-- Подключаем CSS ошибок --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/errors.css') }}">
@endpush

@section('title', 'Ошибка ' . ($code ?? ''))

@section('content')
    <div class="error-container">

        <div class="error-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>

        {{-- Код ошибки --}}
        <h1 class="error-title">Ошибка @yield('code', '!')</h1>

        {{-- Текст ошибки --}}
        <p class="error-text">
            @yield('message', 'Произошла неизвестная ошибка.')
        </p>

        <div class="error-actions">
            {{-- Кнопка "На главную" --}}
            <a href="{{ route('dashboard') }}" class="btn-primary">
                <i class="bi bi-house"></i> На главную
            </a>

            {{-- Кнопка "В каталог" (берем настройки прямо из конфига b2b_menu) --}}
            @if(config('b2b_menu.catalog'))
                <a href="{{ url(config('b2b_menu.catalog.url')) }}" class="btn-secondary">
                    {{-- Иконка из конфига (bi-grid-3x3-gap-fill) --}}
                    <i class="bi {{ config('b2b_menu.catalog.icon') }}"></i>
                    {{-- Текст из конфига (Каталог) --}}
                    В {{ mb_strtolower(config('b2b_menu.catalog.title')) }}
                </a>
            @endif
        </div>

    </div>
@endsection