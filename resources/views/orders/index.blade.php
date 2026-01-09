@extends('layouts.app')

@section('title', 'Мои заказы')

@section('content')
<div class="breadcrumbs">
    <a href="{{ route('dashboard') }}">Главная</a> → <span>Мои заказы</span>
</div>

<h1 class="page-title">Мои заказы</h1>
<p class="page-subtitle">История и текущие статусы ваших покупок</p>

@if($currentCartStats && $currentCartStats->count > 0)
    <div class="cart-alert-panel">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div class="cart-alert-icon">
                <i class="bi bi-cart-check"></i>
            </div>
            <div>
                <div style="font-weight: 600; color: #001F33;">У вас есть незавершенный заказ</div>
                <div style="font-size: 13px; color: #666; line-height: 1.5;">
                    <div>Организация: {{ $currentOrg->name ?? 'Физическое лицо' }}</div>
                    <div>В корзине лежит <b>{{ $currentCartStats->count }}</b> {{ trans_choice('позиция|позиции|позиций', $currentCartStats->count, [], 'ru') }} 
                    (всего {{ (int)$currentCartStats->total_qty }}&nbsp;шт.)</div>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 12px;">
            <button type="button" class="btn-secondary btn-mid" 
                    onclick="openModal('universalConfirm', () => { document.getElementById('clear-cart-form').submit(); }, 'Очистка корзины', 'Вы уверены, что хотите полностью очистить текущую корзину?', 5, 'Да, очистить')">
                Очистить
            </button>
            
            <a href="{{ route('cart.index') }}" class="btn-primary btn-mid">
                Перейти в корзину →
            </a>
        </div>
    </div>

    <form id="clear-cart-form" action="{{ route('cart.clear') }}" method="POST" style="display: none;">
        @csrf
    </form>
@endif

@if($orders->isEmpty())
    <div class="empty-block">
        У вас пока нет оформленных заказов.
    </div>
@else
    <div class="store-table-wrapper">
        <table class="store-table">
            <thead>
                <tr>
                    <th style="width: 220px;">Заказ</th>
                    <th>Плательщик</th>
                    <th style="text-align: center; width: 140px;">Статус</th>
                    <th style="text-align: right; width: 150px;">Сумма</th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>
                        <div class="order-main-info">
                            <div style="font-weight: 500; color: #001F33;">Создан:&nbsp;{{ date('d.m.Y H:i', strtotime($order->created_at)) }}</div>
                            <div class="order-secondary-info">№&nbsp;{{ $order->order_code }}</div>
                            
                            {{-- Последняя активность (если есть в БД) --}}
                            @if($order->last_activity_at && date('YmdHi', strtotime($order->last_activity_at)) > date('YmdHi', strtotime($order->created_at)))
                                    <div class="order-secondary-info" style="color: #3295D1; font-weight: 500;">
                                        Обновлён:&nbsp;{{ date('d.m.Y H:i', strtotime($order->last_activity_at)) }}
                                    </div>
                                @endif
                        </div>
                    </td>
                    <td>
                        <div class="order-main-info">
                            @if($order->org_id)
                                <div style="font-weight: 500;">
                                    {{ $order->org_name }}
                                    {{-- Проверка на физическое отсутствие ИЛИ наличие метки удаления (Soft Delete) --}}
                                    @php
                                        $orgExists = \DB::table('b2b_organizations')
                                            ->where('id', $order->org_id)
                                            ->whereNull('deleted_at')
                                            ->exists();
                                    @endphp
                                    
                                    @if(!$orgExists)
                                        <span class="order-status cancel" style="font-size: 10px; padding: 2px 5px; vertical-align: middle; margin-left: 5px;">организация удалена</span>
                                    @endif
                                </div>
                                <div class="order-secondary-info">ИНН:&nbsp;{{ $order->org_inn }}</div>
                            @else
                                {{-- Логика для физлица --}}
                                <div style="font-weight: 500;">
                                    {{ $order->user_full_name ?? 'Частное лицо' }}
                                    @if(is_null($order->user_phone) && $order->user_id)
                                        <span class="order-status cancel" style="font-size: 10px; padding: 2px 5px; vertical-align: middle; margin-left: 5px;">профиль удален</span>
                                    @endif
                                </div>
                                @if(!empty($order->user_phone))
                                    <div class="order-secondary-info">Тел.:&nbsp;{{ $order->user_phone }}</div>
                                @endif
                            @endif
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <span class="order-status {{ $order->status }}">
                            @switch($order->status)
                                @case('new') Новый @break
                                @case('processing') В&nbsp;работе @break
                                @case('done') Выполнен @break
                                @case('cancel') Отменен @break
                                @default {{ $order->status }}
                            @endswitch
                        </span>
                    </td>
                    <td style="text-align: right; font-weight: 700;" class="text-nowrap">
                        {!! str_replace(' ', '&nbsp;', number_format($order->total_amount, 2, ',', ' ')) !!}&nbsp;₽
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('orders.show', $order->order_code) }}" class="btn-primary btn-mid">
                            Открыть
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 25px;">
        {{ $orders->links() }}
    </div>
@endif
<div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
    <a href="{{ route('dashboard') }}" class="btn-link-back">← Вернуться в личный кабинет</a>
</div>
@endsection