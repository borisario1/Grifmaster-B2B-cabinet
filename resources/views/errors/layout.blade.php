@extends('layouts.error')

{{-- Подключаем CSS ошибок --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/errors.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
@endpush

{{-- Пытаемся взять код из секции 'code', если пусто — из переменной $code, если совсем пусто — просто 'Ошибка' --}}
@section('title', 'Ошибка ' . ($__env->yieldContent('code') ?: ($code ?? '')) . ' — ' . config('b2b.app_name'))

@section('content')
    <div class="error-container">

        <div class="error-icon">
            <i class="bi bi-exclamation-triangle-fill" style="color: #3295D1; font-size: 64px;"></i>
        </div>

        {{-- Код ошибки --}}
        <h1 class="error-title" style="font-size: 48px; font-weight: 700; color: #001F33; margin-top: 20px;">
            Ошибка @yield('code', '!')
        </h1>

        {{-- Текст ошибки --}}
        <p class="error-text" style="font-size: 18px; color: #666; margin-bottom: 35px; max-width: 500px; margin-left: auto; margin-right: auto;">
            @yield('message', 'Произошла неизвестная ошибка.')
        </p>

        <div class="error-actions" style="display: flex; gap: 15px; justify-content: center;">
            {{-- Кнопка "На главную" — Акцентная --}}
            <a href="{{ route('dashboard') }}" class="btn-primary btn-big">
                <i class="bi bi-house"></i> На главную
            </a>

            {{-- Кнопка "В каталог" — Второстепенная --}}
            @if(config('b2b_menu.catalog'))
                <a href="{{ url(config('b2b_menu.catalog.url')) }}" class="btn-secondary btn-big">
                    <i class="bi {{ config('b2b_menu.catalog.icon') }}"></i>
                    {{ config('b2b_menu.catalog.title') }}
                </a>
            @endif
        </div>

        <div style="margin-top: 50px;">
            <a href="javascript:history.back()" class="btn-link-back">← Вернуться назад</a>
        </div>

    </div>
@endsection