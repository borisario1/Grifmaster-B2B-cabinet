@extends('layouts.app')

@section('title', 'Заказ №' . $order->order_code)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
    <style>
        /* Легкие локальные стили для таймлайна, если их еще нет в CSS */
        .order-timeline { position: relative; padding-left: 20px; border-left: 2px solid #eee; margin-left: 10px; }
        .order-timeline-item { position: relative; margin-bottom: 25px; padding-left: 15px; }
        .tl-dot { position: absolute; left: -21px; top: 5px; width: 10px; height: 10px; background: #3295D1; border-radius: 50%; border: 2px solid #fff; }
        .order-timeline-time { font-size: 13px; color: #999; margin-bottom: 4px; }
        .order-section-title { font-weight: 600; font-size: 16px; color: #001F33; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .order-data-list div { margin-bottom: 8px; font-size: 14px; }
    </style>
@endpush

@section('content')
<div class="breadcrumbs">
    <a href="{{ route('dashboard') }}">Главная</a> → 
    <a href="{{ route('orders.index') }}">Мои заказы</a> → 
    <span>Заказ {{ $order->order_code }}</span>
</div>

<h1 class="page-title">Заказ № {{ $order->order_code }}</h1>
<p class="page-subtitle">Подробная информация и история изменений</p>

{{-- ВЕРХНИЕ БЛОКИ --}}
<div class="form-two" style="margin-bottom: 30px;">
    <div class="card-info" style="margin-bottom: 0;">
        <div class="order-section-title">
            <i class="bi bi-info-circle"></i> Основная информация
        </div>
        <div class="order-data-list">
            <div>Статус: <span class="order-status" style="background-color: {{ $order->status_color ?? '#ccc' }}; color: #fff; border: none;">
                {{ $order->status_label ?? $order->status }}
            </span></div>
            <div class="info-row"><span>Дата создания:</span> <strong>{{ date('d.m.Y H:i', strtotime($order->created_at)) }}</strong></div>
            <div class="info-row"><span>Сумма к оплате:</span> <strong style="color: #0B466E;">{!! str_replace(' ', '&nbsp;', number_format($order->total_amount, 2, ',', ' ')) !!}&nbsp;₽</strong></div>
        </div>
    </div>

    <div class="card-info" style="margin-bottom: 0;">
        <div class="order-section-title">
            <i class="bi bi-building"></i> Реквизиты плательщика
        </div>
        <div class="order-data-list">
            @if($order->org_id)
                <div class="info-row"><span>Организация:</span> <strong>{{ $order->org_name }}</strong></div>
                {{-- Проверка на удаление --}}
                @if(!\DB::table('b2b_organizations')->where('id', $order->org_id)->whereNull('deleted_at')->exists())
                    <div style="margin-top: -5px; margin-bottom: 10px;">
                        <span class="order-status cancel" style="font-size: 10px; padding: 2px 5px;">организация удалена</span>
                    </div>
                @endif
                <div class="info-row"><span>ИНН / КПП:</span> <strong>{{ $order->org_inn }} / {{ $order->org_kpp ?? '—' }}</strong></div>
            @else
                <div class="info-row"><span>Тип:</span> <strong>Частное лицо</strong></div>
                <div class="info-row"><span>ФИО:</span> <strong>{{ $order->user_full_name ?? '—' }}</strong></div>
            @endif
        </div>
    </div>
</div>

{{-- ТАБЛИЦА СОСТАВА --}}
<div class="card-info" style="margin-bottom: 30px;">
    <h3 class="section-title" style="margin-bottom: 20px;"><i class="bi bi-cart3"></i> Состав заказа</h3>
    <div class="store-table-wrapper">
        <table class="store-table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">#</th>
                    {{-- Здесь будет колонка для фото после миграции --}}
                    <th>Товар</th>
                    <th style="text-align: center;">Кол-во</th>
                    <th style="text-align: right;">Цена</th>
                    <th style="text-align: right;">Сумма</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td style="color: #999; text-align: center;">{{ $loop->iteration }}</td>
                    <td>
                        <div class="store-name" style="font-weight: 500;">{{ $item->name }}</div>
                        <div style="font-size: 13px; color: #888;">
                            Артикул:&nbsp;<b>{{ $item->article }}</b>
                        </div>
                    </td>
                    <td style="text-align: center;">{{ $item->qty }}&nbsp;шт.</td>
                    <td style="text-align: right;">{!! str_replace(' ', '&nbsp;', number_format($item->price, 2, ',', ' ')) !!}&nbsp;₽</td>
                    <td style="text-align: right; font-weight: 600;">{!! str_replace(' ', '&nbsp;', number_format($item->price * $item->qty, 2, ',', ' ')) !!}&nbsp;₽</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right; font-size: 16px; padding-top: 20px;">Итого к оплате:</td>
                    <td style="text-align: right; font-size: 18px; font-weight: 700; color: #0B466E; padding-top: 20px;">
                        {!! str_replace(' ', '&nbsp;', number_format($order->total_amount, 2, ',', ' ')) !!}&nbsp;₽
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ИСТОРИЯ ЗАКАЗА --}}
<div class="card-info">
    <h3 class="section-title" style="margin-bottom: 25px;"><i class="bi bi-clock-history"></i> История обработки</h3>
    <div class="order-timeline">
        @foreach($history as $h)
        <div class="order-timeline-item">
            <div class="tl-dot"></div>
            <div class="order-timeline-time">{{ date('d.m.Y H:i', strtotime($h->created_at)) }}</div>
            <div style="font-weight: 500; color: #001F33;">{{ $h->message }}</div>
            @if($h->status_to)
                <div style="font-size: 13px; color: #666; margin-top: 4px;">
            @if($h->status_to_label)
                <div style="font-size: 13px; color: #666; margin-top: 4px;">
                    Статус: <span class="order-status" style="background-color: {{ $h->status_to_color ?? '#ccc' }}; color: #fff; border: none; font-size: 11px; padding: 2px 6px;">
                        {{ $h->status_to_label }}
                    </span>
                </div>
            @elseif($h->status_to)
                <div style="font-size: 13px; color: #666; margin-top: 4px;">
                    Статус: {{ $h->status_to }}
                </div>
            @endif
                </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

<div style="margin-top: 35px; border-top: 1px solid #eee; padding-top: 15px;">
    <a href="{{ route('orders.index') }}" class="btn-link-back">← Вернуться к списку заказов</a>
</div>
@endsection