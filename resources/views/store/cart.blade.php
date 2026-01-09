@extends('layouts.app')

@section('title', 'Мой заказ')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
    <style>
        .cart-item-details { font-size: 13px; color: #001F33; margin-top: 5px; background: #F5F5F5; padding: 6px 10px; border-radius: var(--xxmin_radius); display: inline-block; }
        .cart-item-details span { margin-right: 8px; white-space: nowrap; }
        .economy-text { color: #3295D1; font-weight: 500; }
        /* Кнопка очистки в шапке (текстовая) */
        .btn-clear-cart { background: none; border: none; color: #d9534f; cursor: pointer; text-decoration: underline; font-size: 14px; padding: 0; font-weight: 300; transition: 0.2s; }
        .btn-clear-cart:hover { color: #c9302c; text-decoration: none; }
        .col-number { width: 30px; text-align: center; color: #999; font-size: 13px; }
    </style>
@endpush

@section('content')
<div class="breadcrumbs">
    <a href="{{ route('dashboard') }}">Главная</a> →
    <a href="{{ route('catalog.index') }}">Каталог товаров</a> →
    <span>Мой заказ</span>
</div>

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px;">
    <div>
        <h1 class="page-title">Мой заказ</h1>
        <p class="page-subtitle">Проверьте позиции перед оформлением</p>
    </div>
    @if(!$items->isEmpty())
        <button type="button" class="btn-clear-cart" 
                onclick="openModal('universalConfirm', clearCart, 'Очистка корзины', 'Вы уверены, что хотите очистить корзину и удалить свой заказ?', 5, 'Да, очистить')">
            <i class="bi bi-trash3"></i> Очистить корзину
        </button>
    @endif
</div>

@if($items->isEmpty())
    <div class="empty-block">
        <p>Ваша корзина сейчас пуста.</p>
        <div style="margin-top: 25px;">
            <a href="{{ route('catalog.index') }}" class="btn-primary btn-big">
                <i class="bi bi-grid-3x3-gap-fill"></i> Перейти в каталог
            </a>
        </div>
    </div>
@else
    <div class="store-table-wrapper">
        <table class="store-table">
            <thead>
                <tr>
                    <th class="col-number">#</th>
                    <th>Фото</th>
                    <th>Артикул, наименование и цены</th>
                    <th>Сумма</th>
                    <th>Доступно</th>
                    <th style="width: 140px;">В заказе</th>
                    <th style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($items as $item)
                @php
                    $rrc = $item->product->price;
                    $partnerPrice = $item->product->getPartnerPrice(0); // Пока скидки 0
                    $discount = 0; 
                    $savingPerUnit = $rrc - $partnerPrice;
                    $totalSaving = $savingPerUnit * $item->qty;
                @endphp
                <tr>
                    <td class="col-number">{{ $loop->iteration }}</td>
                    <td style="vertical-align: middle; text-align: center; width: 80px;">
                                <div style="position: relative; display: inline-block;">
                                    <img class="store-img" src="{{ $item->product->image_url }}" 
                                        style="width: 70px; height: 70px; #eee;">
                                    <div style="margin-top: 5px; font-size: 13px; color: #7f8c8d; font-weight: 400;">
                                        {{ $item->qty }}&nbsp;шт.
                                    </div>
                                </div>
                            </td>
                    <td class="store-name">
                        <div style="font-weight: 500;">
                            {{ $item->product->name }}
                        </div>
                        <div class="cart-item-details" style="font-weight: 300;">
                            <span>Артикул:&nbsp;<b style="font-weight: 500;">{{ $item->product->article }}</b></span><br>
                            <span>РРЦ:&nbsp;<b>{!! str_replace(' ', '&nbsp;', number_format($rrc, 2, ',', ' ')) !!}&nbsp;₽</b></span>
                            <span>ОПТ:&nbsp;<b style="font-weight: 500;">{!! str_replace(' ', '&nbsp;', number_format($partnerPrice, 2, ',', ' ')) !!}&nbsp;₽</b></span><br>
                            <span>Скидка:&nbsp;{{ $discount }}%</span>
                            <span>Выгода:&nbsp;<b class="economy-text">{!! str_replace(' ', '&nbsp;', number_format($totalSaving, 2, ',', ' ')) !!}&nbsp;₽</b></span>
                        </div>
                    </td>
                    <td><b>{!! str_replace(' ', '&nbsp;', number_format($partnerPrice * $item->qty, 2, ',', ' ')) !!}&nbsp;₽</b></td>
                    <td>{{ $item->product->free_stock ?? '—' }}</td>
                    <td>
                        <form class="ajax-cart-form" method="POST" action="{{ route('cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                            <input type="hidden" name="mode" value="set"> 
                            <div style="display: flex; gap: 15px;">
                                <input type="number" min="1" name="qty" class="store-qty-input" value="{{ $item->qty }}">
                                <button type="submit" class="btn-primary btn-sm">ОК</button>
                            </div>
                        </form>
                    </td>
                    <td>
                        <button class="btn-primary btn-sm" style="background:#d9534f; border-color:#d9534f;" 
                                onclick="openModal('universalConfirm', () => { removeItem({{ $item->product_id }}) }, 'Удаление товара', 'Вы уверены, что хотите удалить из корзины весь товар «{{ $item->product->name }}»?', 0, 'Да, удалить')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    
    {{-- 1. БЛОК ПЛАТЕЛЬЩИКА (На всю ширину) --}}
    <div class="profile-section" style="margin-top: 35px;">
        <h2 class="section-title"><i class="bi bi-building"></i> Информация о плательщике</h2>
        <div class="card-info">
            @if($currentOrg)
                <div class="info-row">
                    <span>Организация:</span>
                    <strong>{{ $currentOrg->name }}</strong>
                </div>
                <div class="info-row">
                    <span>ИНН / КПП:</span>
                    <strong>{{ $currentOrg->inn }} / {{ $currentOrg->kpp ?: '—' }}</strong>
                </div>
                <div class="info-row">
                    <span>Юридический адрес:</span>
                    <strong>{{ $currentOrg->address ?: '—' }}</strong>
                </div>
            @else
                <div class="info-row">
                    <span>Тип плательщика:</span>
                    <strong>Физическое лицо</strong>
                </div>
                <div class="info-row">
                    <span>ФИО заказчика:</span>
                    <strong>{{ $profile->first_name ?? '' }} {{ $profile->last_name ?? $user->email }}</strong>
                </div>
                <div class="info-row">
                    <span>Контактный телефон:</span>
                    <strong>{{ $profile->work_phone ?? '—' }}</strong>
                </div>
            @endif
            <div class="info-row">
                <span>Email для связи:</span>
                <strong>{{ $user->email }}</strong>
            </div>
        </div>
    </div>

    {{-- 2. ИТОГ ЗАКАЗА И КНОПКА (Узкий блок справа) --}}
    <div style="display: flex; justify-content: flex-end; margin-top: 25px;">
        <div class="cart-summary-block" style="width: 100%; max-width: 450px;">
            <h3 class="cart-summary-title">ИТОГ К ОПЛАТЕ</h3>
            <div class="cart-summary-row">
                <span>Уникальных позиций:</span>
                <strong>{{ $summary['pos'] }}</strong>
            </div>
            <div class="cart-summary-row">
                <span>Общее количество:</span>
                <strong>{{ $summary['qty'] }} шт.</strong>
            </div>
            
            <div class="cart-summary-row total">
                <span>Итоговая сумма:</span>
                <strong>{!! str_replace(' ', '&nbsp;', number_format($summary['amount'], 2, ',', ' ')) !!} ₽</strong>
            </div>
            
            <button class="btn-primary btn-big" style="width: 100%; margin-top: 20px;"
                onclick="openModal('universalConfirm', submitOrder, 'Подтверждение заказа', 'Вы уверены, что хотите отправить заказ на сумму {{ number_format($summary['amount'], 2, ',', ' ') }} ₽? Данные будут переданы в отдел продаж.', 0, 'Оформить заказ')">
                <i class="bi bi-check2-circle"></i> Оформить заказ
            </button>
        </div>
    </div>

    <form id="checkout-form" action="{{ route('cart.checkout') }}" method="POST" style="display: none;">@csrf</form>
@endif

<div style="margin-top: 35px; border-top: 1px solid #eee; padding-top: 15px;">
    <a href="{{ route('catalog.index') }}" class="btn-link-back">← Вернуться в каталог товаров</a>
</div>
@endsection

@push('scripts')
<script>
    // AJAX обновление количества
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('.ajax-cart-form');
        if (!form) return;
        e.preventDefault();
        
        const btn = form.querySelector('button');
        const formData = new FormData(form);
        btn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`Количество обновлено`, 'bi-check-circle');
                setTimeout(() => location.reload(), 800);
            }
        });
    });

    function removeItem(productId) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('qty', 0);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("cart.add") }}', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(() => {
            showToast('Товар удален', 'bi-trash');
            setTimeout(() => location.reload(), 800);
        });
    }

    function clearCart() {
        fetch('{{ route("cart.clear") }}', { 
            method: 'POST', 
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' } 
        })
        .then(() => {
            showToast('Корзина очищена', 'bi-trash3');
            setTimeout(() => location.reload(), 800);
        });
    }

    function submitOrder() {
        openModal('universalConfirm', null, 'Создание заказа...', 'Пожалуйста, подождите...', 0, '', true);
        document.getElementById('checkout-form').submit();
    }
</script>
@endpush