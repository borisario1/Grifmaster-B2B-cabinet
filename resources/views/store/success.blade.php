@extends('layouts.app')

@section('title', 'Заказ №' . $order->order_code . ' оформлен')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
    <style>
        /* Выносим оформление бокса в стили, чтобы HTML был чище */
        .order-success-box {
            background: #f0fdf4; 
            border: 1px solid #cff8ddff;
            border-left: 4px solid #28a745;
            border-radius: var(--min_radius);
            padding: 30px;
            margin: 30px 0;
        }
        .order-success-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #166534;
        }
        .order-success-text {
            font-size: 16px;
            font-weight: 300;
            line-height: 1.6;
            color: #155724;
            margin-left: 47px; /* Выравнивание текста под заголовок (32px иконка + 15px gap) */
        }
    </style>
@endpush

@section('content')
<div class="breadcrumbs">
    <a href="{{ route('dashboard') }}">Главная</a> →
    <a href="{{ route('orders.index') }}">Мои заказы</a> →
    <span>Успешно</span>
</div>

<h1 class="page-title">Заказ оформлен!</h1>
<p class="page-subtitle">Ваш заказ успешно создан и передан менеджеру для обработки.</p>

<div class="order-success-box">
    <div class="order-success-title">
        <i class="bi bi-check-circle-fill" style="font-size: 32px; color: #28a745;"></i>
        Заказ № {{ $order->order_code }} принят
    </div>

    <div class="order-success-text">
        Благодарим за заказ! Информацию о нем мы уже отправили на ваш Email.<br>
        Менеджер свяжется с вами в ближайшее время для подтверждения наличия и уточнения деталей доставки.
    </div>
</div>

<div style="display: flex; gap: 15px; margin-top: 30px;">
    {{-- Наша стандартная большая кнопка --}}
    <a href="{{ route('orders.index') }}" class="btn-primary btn-big">
        <i class="bi bi-receipt"></i> К списку заказов
    </a>

    {{-- Используем btn-default для второй кнопки (темная из твоих стилей) --}}
    <a href="{{ route('catalog.index') }}" class="btn-default btn-big">
        <i class="bi bi-grid-3x3-gap-fill"></i> Вернуться в каталог
    </a>
</div>

{{-- Отступ и системная ссылка возврата --}}
<div style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
    <a href="{{ route('dashboard') }}" class="btn-link-back">
        ← В личный кабинет
    </a>
</div>
@endsection