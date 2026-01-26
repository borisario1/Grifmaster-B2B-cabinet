@extends('layouts.app')

@section('title', 'Каталог товаров')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
    <style>
        .filter-checkbox-group { display: flex; gap: 20px; align-items: center; padding-top: 10px; }
        .filter-checkbox-group label { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; font-weight: 500; }
        .filter-checkbox-group input { width: 18px; height: 18px; accent-color: #0B466E; }
        .hidden-data { display: none !important; }
    </style>
@endpush

@section('content')
{{-- Сообщение о том что каталог загружается --}}
<div id="loading-overlay" class="custom-modal-wrapper" style="display: flex;">
    <div class="custom-modal-backdrop"></div>
    <div class="custom-modal-content" style="max-width: 450px;">
        <div class="custom-modal-body" style="text-align: center; padding: 50px 40px;">
            {{-- Большая фоновая иконка для веса --}}
            <div style="margin-bottom: 25px;">
                <i class="bi bi-boxes" style="font-size: 68px; color: #0B466E; opacity: 0.2; position: absolute; transform: translate(-50%, -10px); z-index: 0;"></i>
                <div class="loader-spinner" style="width: 50px; height: 50px; border-width: 5px; border-top-color: #3295D1; margin: 0 auto; position: relative; z-index: 1;"></div>
            </div>

            <h2 style="margin-bottom: 12px; color: #001F33; position: relative; z-index: 1;">Каталог открывается</h2>
            <p style="color: #64748b; font-size: 18px; line-height: 1.5; margin: 0;">
                Пожалуйста, подождите немного.<br>
                Остатки и цены обновляются...
            </p>
        </div>
    </div>
</div>

<div id="store-app">
    {{-- Хлебные крошки --}}
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> 
        → <a href="{{ route('catalog.index') }}">Каталог товаров</a>
        @if(isset($isWishlist) && $isWishlist)
            → <span>Избранное</span>
        @endif
    </div>

    {{-- ШАПКА: Заголовок слева, Переключатель справа --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h1 class="page-title m-0">{{ $pageTitle ?? 'Каталог товаров' }}</h1>

        {{-- ПЕРЕКЛЮЧАТЕЛЬ РЕЖИМОВ --}}
        <div class="mode-switch-container">
            
            {{-- 1. Все товары --}}
            <a href="{{ route('catalog.index') }}" 
               class="mode-switch-btn {{ request()->routeIs('catalog.index') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Все товары
            </a>

            {{-- 2. Избранное (Wishlist) --}}
            <a href="{{ route('catalog.wishlist') }}" 
               class="mode-switch-btn {{ request()->routeIs('catalog.wishlist') ? 'active' : '' }}">
                <i class="bi bi-star-fill" 
                   style="{{ request()->routeIs('catalog.wishlist') ? 'color: #f59e0b;' : '' }}"></i> 
                Избранное
            </a>

            {{-- 3. Понравилось (Likes) --}}
            {{-- Убедитесь, что роут catalog.liked существует, или замените на свой --}}
            <a href="{{ route('catalog.liked') }}" 
               class="mode-switch-btn {{ request()->routeIs('catalog.liked') ? 'active' : '' }}">
                <i class="bi bi-heart-fill" 
                   style="{{ request()->routeIs('catalog.liked') ? 'color: #ef4444;' : '' }}"></i> 
                Понравилось
            </a>

            {{-- 4. Ранее заказывали (History/Orders) --}}
            {{-- Убедитесь, что роут catalog.ordered существует --}}
            <a href="{{ route('catalog.ordered') }}" 
               class="mode-switch-btn {{ request()->routeIs('catalog.ordered') ? 'active' : '' }}">
                <i class="bi bi-bag-check-fill" 
                   style="{{ request()->routeIs('catalog.ordered') ? 'color: #10b981;' : '' }}"></i> 
                Ранее заказывали
            </a>

            {{-- 5. Недавно смотрели (Viewed) --}}
            {{-- Убедитесь, что роут catalog.viewed существует --}}
            <a href="{{ route('catalog.viewed') }}" 
               class="mode-switch-btn {{ request()->routeIs('catalog.viewed') ? 'active' : '' }}">
                <i class="bi bi-eye-fill" 
                   style="{{ request()->routeIs('catalog.viewed') ? 'color: #3b82f6;' : '' }}"></i> 
                Недавно смотрели
            </a>
            
        </div>
    </div>

    <div class="store-filter-panel">
        {{-- ВЕРХНИЙ РЯД: Все инпуты в одну линию на десктопе (lg) --}}
        <div class="row g-3">
            {{-- Поиск (3/12) --}}
            <div class="col-lg-3 col-md-6 col-12">
                <label class="store-filter-label">Живой поиск</label>
                <input type="text" class="search store-qty-input" placeholder="Артикул или название..." style="width: 100%;">
            </div>
            
            {{-- Бренды (3/12) --}}
            <div class="col-lg-3 col-md-6 col-12">
                <label class="store-filter-label" style="margin-top: 10px;">Бренды</label>
                <div id="brand-tags-container" style="display: flex; flex-wrap: wrap; gap: 5px;">
                    @foreach($brands as $b)
                        <span class="brand-tag btn btn-secondary" 
                            data-brand="{{ $b }}" 
                            style="height: 32px;">{{ $b }}</span>
                    @endforeach
                </div>
            </div>

            {{-- 3 ФИЛЬТРА В РЯД (2/12 каждый) --}}
            <div class="col-lg-2 col-md-4 col-12">
                <label class="store-filter-label" style="margin-top: 10px;">Коллекция</label>
                <select id="f-coll" class="store-qty-input" style="width: 100%;">
                    <option value="">Все коллекции</option>
                    @foreach($collections as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-12">
                <label class="store-filter-label" style="margin-top: 10px;">Категория</label>
                <select id="f-cat" class="store-qty-input" style="width: 100%;">
                    <option value="">Все категории</option>
                    @foreach($categories as $cat) <option value="{{ $cat }}">{{ $cat }}</option> @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-12">
                <label class="store-filter-label" style="margin-top: 10px;">Тип товара</label>
                <select id="f-type" class="store-qty-input" style="width: 100%;">
                    <option value="">Все типы</option>
                    @foreach($types as $t) <option value="{{ $t }}">{{ $t }}</option> @endforeach
                </select>
            </div>
        </div>

        {{-- НИЖНИЙ РЯД: Чекбоксы и Кнопка сброса --}}
        <div class="row g-3 mt-1 align-items-center">
            <div class="col-md-10">
                <div class="filter-checkbox-group">
                    <label><input type="checkbox" id="f-stock"> В наличии</label>
                    <label><input type="checkbox" id="f-no-coll"> Без коллекции</label>
                </div>
            </div>
            <div class="col-md-2 text-end">
                {{-- Кнопка осталась без изменений --}}
                <button onclick="resetFilters()" class="btn-primary btn-big" style="margin-top: 23px;" title="Сбросить">
                    Очистить фильтр
                </button>
            </div>
        </div>

    <div class="store-table-wrapper">
        <table class="store-table">
            <thead>
                <tr>
                    <th>Фото</th>
                    <th class="sort" data-sort="js-art" style="cursor:pointer;">
                        Артикул <i class="bi bi-arrow-down-up" style="font-size: 12px; opacity: 0.5;"></i>
                    </th>
                    <th class="sort" data-sort="js-name" style="cursor:pointer;">
                        Название <i class="bi bi-arrow-down-up" style="font-size: 12px; opacity: 0.5;"></i>
                    </th>
                    <th class="sort" data-sort="js-brand" style="cursor:pointer;">
                        Бренд <i class="bi bi-arrow-down-up" style="font-size: 12px; opacity: 0.5;"></i>
                    </th>
                    <th class="sort" data-sort="js-stock" style="cursor:pointer;">
                        Остаток <i class="bi bi-arrow-down-up" style="font-size: 12px; opacity: 0.5;"></i>
                    </th>
                    <th class="sort" data-sort="js-price" style="cursor:pointer;">
                        Цена <i class="bi bi-arrow-down-up" style="font-size: 12px; opacity: 0.5;"></i>
                    </th>
                    <th>Опт</th>
                    <th style="width:120px;">Кол-во</th>
                </tr>
            </thead>
            <tbody class="list">
                @foreach ($products as $i)
                    <tr>
                        {{-- 1. ФОТО + ГЛАЗ --}}
                        <td style="width: 80px;">
                            <div class="store-img-wrapper" onclick="openProductModal({{ $i->id }})">
                                <img src="{{ $i->image_url }}" class="store-img" loading="lazy">
                                <div class="btn-quick-view-overlay">
                                    <i class="bi bi-eye-fill"></i>
                                </div>
                            </div>
                        </td>

                        {{-- 2. АРТИКУЛ + КНОПКИ (С учетом состояния при загрузке) --}}
                        <td class="js-art">
                            <div style="font-weight: 600; color: #334155;">{{ $i->article }}</div>
                            <div class="sku-actions">
                                {{-- Лайк --}}
                                <button type="button" 
                                        class="btn-action-small btn-like btn-like-{{ $i->id }} {{ $i->is_liked ? 'is-active' : '' }}" 
                                        onclick="toggleLike(this, {{ $i->id }})" 
                                        title="Нравится">
                                    <i class="bi {{ $i->is_liked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                </button>

                                {{-- Избранное --}}
                                <button type="button" 
                                        class="btn-action-small btn-fav btn-fav-{{ $i->id }} {{ $i->is_in_wishlist ? 'is-active' : '' }}" 
                                        onclick="toggleWishlist(this, {{ $i->id }})" 
                                        title="В избранное">
                                    <i class="bi {{ $i->is_in_wishlist ? 'bi-star-fill' : 'bi-star' }}"></i>
                                </button>
                            </div>
                        </td>

                        {{-- 3. НАЗВАНИЕ + МЕТРИКИ --}}
                        <td class="store-name">
                            <span class="js-name store-name-link" onclick="openProductModal({{ $i->id }})">
                                {{ $i->name }}
                            </span>
                            
                            <span class="js-coll hidden-data">{{ $i->collection ?? 'Без названия' }}</span>
                            <span class="js-cat hidden-data">{{ $i->product_category }}</span>
                            <span class="js-type hidden-data">{{ $i->product_type }}</span>

                            <div class="product-meta-row">
                                <span class="meta-item" title="Просмотры">
                                    <i class="bi bi-eye"></i> {{ $i->details->views_count ?? 0 }}
                                </span>
                                
                                {{-- Лайки --}}
                                @if(($i->details->likes_count ?? 0) > 0)
                                    <span class="meta-item" title="Лайки">
                                        <i class="bi bi-heart-fill" style="color: #e11d48; opacity: 0.7;"></i> {{ $i->details->likes_count }}
                                    </span>
                                @endif

                                {{-- Избранное (Глобальное) --}}
                                @if(($i->details->wishlist_count ?? 0) > 0)
                                    <span class="meta-item" title="Добавили в избранное">
                                        <i class="bi bi-star-fill" style="color: #f59e0b; opacity: 0.7;"></i> {{ $i->details->wishlist_count }}
                                    </span>
                                @endif
                                
                                {{-- Рейтинг + Отзывы --}}
                                @if(($i->details->rating ?? 0) > 0)
                                    <span class="meta-item rating" title="Рейтинг товара">
                                        <i class="bi bi-star-fill"></i> {{ $i->details->rating }}
                                        <span style="color: #94a3b8; font-weight: 400; margin-left: 3px;">
                                            ({{ $i->details->rating_count ?? 0 }})
                                        </span>
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td class="js-brand">{{ $i->brand }}</td>
                        <td class="js-stock" data-stock="{{ $i->free_stock }}">{{ $i->free_stock }}</td>
                        <td class="js-price" data-price="{{ $i->price }}">{!! str_replace(' ', '&nbsp;', number_format($i->price, 2, ',', ' ')) !!}&nbsp;₽</td>
                        
                        {{-- Опт --}}
                        <td>
                            @if ($i->discount_percent > 0)
                                <b>{!! str_replace(' ', '&nbsp;', number_format($i->partner_price, 2, ',', ' ')) !!}&nbsp;₽</b>
                                <div class="store-discount">скидка {{ (int)$i->discount_percent }}%</div>
                            @else
                                <span class="no-discount">—</span>
                            @endif
                        </td>

                        {{-- 4. ДЕЙСТВИЯ (Очищено от маркетинга) --}}
                        <td class="col-actions">
                            <div class="cart-controls-container">
                                @php $isInCart = $i->in_cart; @endphp
                                <form method="POST" action="{{ route('cart.add') }}" 
                                    class="ajax-cart-form catalog-qty-group {{ $isInCart ? 'is-in-cart' : '' }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $i->id }}">
                                    <input type="hidden" name="mode" value="set">
                                    
                                    <div class="cart-input-group">
                                        <button type="button" class="btn-qty-step" 
                                                onclick="handleMinus(this, {{ $i->id }}, '{{ $i->name }}')">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        
                                        <input type="number" name="qty" min="0" class="qty-field" 
                                            value="{{ $isInCart ? $i->cart_qty : 0 }}"
                                            data-original="{{ $i->in_cart ? $i->cart_qty : 0 }}" 
                                            oninput="handleInput(this)">
                                        
                                        <button type="button" class="btn-qty-step" 
                                                onclick="this.previousElementSibling.stepUp(); handleInput(this.previousElementSibling)">
                                            <i class="bi bi-plus"></i>
                                        </button>

                                        <button type="submit" class="btn-qty-apply" {{ $isInCart ? 'disabled' : '' }}>
                                            <i class="bi {{ $isInCart ? 'bi-check2' : 'bi-cart-plus' }}"></i>
                                        </button>
                                    </div>
                                </form>

                                <button class="btn-cart-remove {{ !$isInCart ? 'hidden-data' : '' }}" 
                                        onclick="openModal('universalConfirm', () => { removeItemInCatalog(this, {{ $i->id }}) }, 'Удаление товара', 'Вы уверены, что хотите удалить из заказа «{{ $i->name }}»?', 0, 'Да, удалить')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- Сообщение о пустом результате (скрыто по умолчанию) --}}
    <div id="no-result-message" style="display: none;" class="alert alert-info text-center mt-4">
    <i class="bi bi-search"></i> 
    <span id="no-result-text">
        @if(isset($isWishlist) && $isWishlist)
            В вашем списке избранного пока нет товаров.
        @else
            По вашему запросу ничего не найдено.
        @endif
    </span>
</div>

    <div class="text-center mt-4 mb-5">
            {{-- Исправленный счетчик: добавили ID для "найдено" --}}
            <p id="showing-info" style="font-size: 14px; color: #666;">
                Найдено <span id="total-found">0</span> товаров. Показано <span id="visible-count">0</span>.
            </p>
            
            <button id="load-more-btn" class="btn btn-primary">
                Показать еще
            </button>
        </div>
    </div>
    
    {{-- Вставляем компонент модалки просмотра ЗДЕСЬ --}}
    <x-modal-product-view />

</div>
@endsection

@push('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>

<script>
    // Подготовка каталога товаров
    window.jsLoaded = false;
    // Ввод данных
    function handleInput(input) {
        const form = input.closest('form');
        const applyBtn = form.querySelector('.btn-qty-apply');
        const applyIcon = applyBtn.querySelector('i');
        const isInCart = form.classList.contains('is-in-cart');
        
        const originalValue = parseInt(input.getAttribute('data-original') || 0);
        const currentValue = parseInt(input.value);

        if (isNaN(currentValue) || currentValue < 0) {
            applyBtn.disabled = true;
            return;
        }

        // Если товар в корзине и значение совпадает (например, оба 5) — кнопка не нужна
        if (isInCart && currentValue === originalValue) {
            applyBtn.disabled = true;
            form.classList.remove('needs-save');
            return;
        }

        // Во всех остальных случаях кнопка активна:
        // 1. Если товара нет в корзине (даже если в поле 0, чтобы сработал "быстрый заказ")
        // 2. Если значение отличается от того, что в корзине (редактирование)
        applyBtn.disabled = false;
        form.classList.add('needs-save');

        if (applyIcon) {
            applyIcon.className = isInCart ? 'bi bi-check2' : 'bi bi-cart-plus';
        }
    }

    // Функция обработки минуса
    function handleMinus(btn, productId, productName) {
        const input = btn.nextElementSibling;
        const currentValue = parseInt(input.value);
        const form = btn.closest('form');
        const isInCart = form.classList.contains('is-in-cart');

        if (currentValue > 1) {
            input.stepDown();
            handleInput(input);
        } else if (currentValue === 1) {
            if (isInCart) {
                // Удаление из корзины
                const removeBtn = form.parentElement.querySelector('.btn-cart-remove');
                openModal('universalConfirm', () => { 
                    removeItemInCatalog(removeBtn, productId);
                    // Внутри removeItemInCatalog у нас уже стоит сброс в 0
                }, 'Удаление товара', `Вы уверены, что хотите удалить из заказа «${productName}»?`, 0, 'Да, удалить');
            } else {
                // Если товара нет в корзине, просто скидываем в 0
                input.value = 0;
                handleInput(input);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const NO_IMAGE = 'https://data.grifmaster.ru/files/dq9/data/noimage.png';

        // 1. ИНИЦИАЛИЗАЦИЯ LIST.JS
        let itemsPerPage = 30; 
        const step = 30;

        const options = {
            valueNames: [
                'js-art', 'js-name', 'js-brand', 'js-coll', 'js-cat', 'js-type',
                { name: 'js-stock', attr: 'data-stock' },
                { name: 'js-price', attr: 'data-price' }
            ],
            page: itemsPerPage
        };

        const storeList = new List('store-app', options);

        // 2. ПЕРЕМЕННЫЕ И ЭЛЕМЕНТЫ UI
        const loadMoreBtn = document.getElementById('load-more-btn');
        const showingInfo = document.getElementById('showing-info');
        const noResultMessage = document.getElementById('no-result-message');
        const noResultText = document.getElementById('no-result-text');
        
        // Переменные фильтров
        const selectedBrands = new Set();
        const brandTags = document.querySelectorAll('.brand-tag');
        const selects = {
            coll: document.getElementById('f-coll'),
            cat: document.getElementById('f-cat'),
            type: document.getElementById('f-type')
        };
        const fStock = document.getElementById('f-stock');
        const fNoColl = document.getElementById('f-no-coll');

        // 3. ФУНКЦИИ СИНХРОНИЗАЦИИ URL (ИСПРАВЛЕНО: перемещено сюда, где переменные уже существуют)
        let isRestoringState = false;

        const updateURL = () => {
            if (isRestoringState) return; // Не меняем URL пока восстанавливаемся

            const params = new URLSearchParams();

            // Поиск
            const searchVal = document.querySelector('.search').value.trim();
            if (searchVal) params.set('q', searchVal);

            // Бренды
            if (selectedBrands.size > 0) {
                params.set('brands', Array.from(selectedBrands).join(','));
            }

            // Селекты
            if (selects.coll.value) params.set('coll', selects.coll.value);
            if (selects.cat.value)  params.set('cat', selects.cat.value);
            if (selects.type.value) params.set('type', selects.type.value);

            // Чекбоксы
            if (fStock.checked) params.set('stock', '1');
            if (fNoColl.checked) params.set('nocoll', '1');

            // Обновляем URL без перезагрузки страницы
            const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.pushState({}, '', newUrl);
        };

        const restoreFromURL = () => {
            isRestoringState = true; // Блокируем запись в историю
            
            const params = new URLSearchParams(window.location.search);

            // 1. Поиск
            if (params.has('q')) {
                const val = params.get('q');
                document.querySelector('.search').value = val;
                storeList.search(val);
            } else {
                // Если в URL нет поиска, но в поле что-то осталось (кэш браузера) — чистим
                document.querySelector('.search').value = '';
                storeList.search();
            }

            // 2. Бренды
            selectedBrands.clear();
            brandTags.forEach(t => t.classList.replace('btn-primary', 'btn-secondary'));
            
            if (params.has('brands')) {
                const brandsFromUrl = params.get('brands').split(',');
                brandsFromUrl.forEach(b => {
                    selectedBrands.add(b);
                    // Ищем кнопку бренда и активируем визуально
                    const tag = document.querySelector(`.brand-tag[data-brand="${b}"]`);
                    if (tag) tag.classList.replace('btn-secondary', 'btn-primary');
                });
            }

            // 3. Селекты
            if (params.has('coll')) selects.coll.value = params.get('coll');
            else selects.coll.value = "";

            if (params.has('cat'))  selects.cat.value  = params.get('cat');
            else selects.cat.value = "";

            if (params.has('type')) selects.type.value = params.get('type');
            else selects.type.value = "";

            // 4. Чекбоксы
            fStock.checked = params.has('stock');
            fNoColl.checked = params.has('nocoll');

            // Применяем фильтры
            applyAllFilters();

            isRestoringState = false; // Снимаем блокировку
        };

        // 4. ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ UI
        function validateImages() {
            const images = document.querySelectorAll('.store-img');
            images.forEach(img => {
                img.onerror = function() { if (this.src !== NO_IMAGE) this.src = NO_IMAGE; };
                if (img.complete && img.naturalWidth === 0) img.src = NO_IMAGE;
                if (!img.getAttribute('src')) img.src = NO_IMAGE;
            });
        }

        function updateLoadMoreUI() {
            const totalItems = storeList.items.length;
            const matched = storeList.matchingItems.length;
            const shown = storeList.visibleItems.length;
            
            if (totalItems === 0) {
                noResultMessage.style.display = 'block';
                noResultText.innerText = "Каталог товаров пуст.";
                if(showingInfo) showingInfo.style.display = 'none';
            } else if (matched === 0) {
                noResultMessage.style.display = 'block';
                noResultText.innerText = "По вашему запросу ничего не найдено";
                if(showingInfo) showingInfo.style.display = 'none';
            } else {
                noResultMessage.style.display = 'none';
                if(showingInfo) {
                    showingInfo.style.display = 'block';
                    document.getElementById('total-found').innerText = matched;
                    document.getElementById('visible-count').innerText = shown;
                }
            }
            
            if (loadMoreBtn) {
                loadMoreBtn.style.display = (shown >= matched || matched === 0) ? 'none' : 'inline-block';
            }
        }

        const updateSelectOptions = () => {
            const filterKeys = ['brand', 'coll', 'cat', 'type'];
            
            filterKeys.forEach(currentKey => {
                const otherActiveFilters = {};
                if (currentKey !== 'brand' && selectedBrands.size > 0) otherActiveFilters.brand = selectedBrands;
                
                Object.keys(selects).forEach(key => {
                    if (key !== currentKey && selects[key].value !== "") {
                        otherActiveFilters[key] = selects[key].value;
                    }
                });
                
                const stockOnly = fStock.checked;
                const noCollOnly = fNoColl.checked;
                const availableOptions = new Set();
                
                storeList.items.forEach(item => {
                    const v = item.values();
                    let isMatch = true;
                    
                    if (otherActiveFilters.brand && !otherActiveFilters.brand.has(v['js-brand'])) isMatch = false;
                    if (otherActiveFilters.coll && v['js-coll'] !== otherActiveFilters.coll) isMatch = false;
                    if (otherActiveFilters.cat && v['js-cat'] !== otherActiveFilters.cat) isMatch = false;
                    if (otherActiveFilters.type && v['js-type'] !== otherActiveFilters.type) isMatch = false;
                    if (stockOnly && parseInt(v['js-stock']) <= 0) isMatch = false;
                    if (noCollOnly && v['js-coll'] !== 'Без названия') isMatch = false;

                    if (isMatch) {
                        const val = v['js-' + currentKey];
                        if (val) availableOptions.add(val);
                    }
                });

                if (currentKey === 'brand') {
                    brandTags.forEach(tag => {
                        const b = tag.getAttribute('data-brand');
                        if (availableOptions.has(b)) {
                            tag.classList.remove('disabled');
                        } else {
                            tag.classList.add('disabled');
                            if (selectedBrands.has(b)) {
                                selectedBrands.delete(b);
                                tag.classList.replace('btn-primary', 'btn-secondary');
                            }
                        }
                    });
                } else {
                    const select = selects[currentKey];
                    const savedValue = select.value;
                    while (select.options.length > 1) select.remove(1);
                    Array.from(availableOptions).sort().forEach(val => {
                        const opt = document.createElement('option');
                        opt.value = val; opt.text = val; select.add(opt);
                    });
                    select.value = savedValue;
                }
            });
        };

        // 5. ГЛАВНАЯ ФУНКЦИЯ ФИЛЬТРАЦИИ
        const applyAllFilters = () => {
            storeList.filter(item => {
                const v = item.values();
                const matchBrand = selectedBrands.size === 0 || selectedBrands.has(v['js-brand']);
                const matchColl  = !selects.coll.value  || v['js-coll'] === selects.coll.value;
                const matchCat   = !selects.cat.value   || v['js-cat'] === selects.cat.value;
                const matchType  = !selects.type.value  || v['js-type'] === selects.type.value;
                const matchStock = !fStock.checked || parseInt(v['js-stock']) > 0;
                const matchNoColl = !fNoColl.checked || (v['js-coll'] === 'Без названия');
                return matchBrand && matchColl && matchCat && matchType && matchStock && matchNoColl;
            });

            // Сброс пагинации
            itemsPerPage = 50;
            storeList.show(1, itemsPerPage);
            
            updateSelectOptions();
            updateLoadMoreUI();
            validateImages();
            
            // ВАЖНО: Обновляем URL при каждом изменении фильтров
            updateURL(); 
        };

        // 6. НАЗНАЧЕНИЕ СЛУШАТЕЛЕЙ СОБЫТИЙ

        // Кнопка "Показать еще"
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                itemsPerPage += step;
                storeList.show(1, itemsPerPage);
                updateLoadMoreUI();
                validateImages();
            });
        }

        // Клик по брендам
        brandTags.forEach(tag => {
            tag.addEventListener('click', function() {
                const brand = this.getAttribute('data-brand');
                if (selectedBrands.has(brand)) {
                    selectedBrands.delete(brand);
                    this.classList.replace('btn-primary', 'btn-secondary');
                } else {
                    selectedBrands.add(brand);
                    this.classList.replace('btn-secondary', 'btn-primary');
                }
                applyAllFilters();
            });
        });

        // Селекты и чекбоксы
        Object.values(selects).forEach(el => el.addEventListener('change', applyAllFilters));
        [fStock, fNoColl].forEach(el => el.addEventListener('change', applyAllFilters));

        // Поиск (событие от List.js)
        storeList.on('searchComplete', updateLoadMoreUI);
        
        // Поиск: Добавляем обновление URL при вводе (с задержкой/debounce)
        const searchInput = document.querySelector('.search');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', function() { // Используем input вместо keyup
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    updateURL(); // Только обновляем URL, сам поиск делает List.js
                }, 800);
            });
        }

        // Кнопка сброса
        window.resetFilters = () => {
            selectedBrands.clear();
            brandTags.forEach(t => t.classList.replace('btn-primary', 'btn-secondary'));
            Object.values(selects).forEach(s => s.value = "");
            fStock.checked = false; 
            fNoColl.checked = false;
            if(searchInput) searchInput.value = "";
            
            storeList.search(); // Сброс поиска в List.js
            applyAllFilters();  // Сброс остальных фильтров + обновление URL (очистка)
        };

        // Слушаем кнопки браузера Назад/Вперед (Popstate)
        window.addEventListener('popstate', function() {
            restoreFromURL();
        });

        // 7. ЗАПУСК ПРИ ЗАГРУЗКЕ
        // Сначала восстанавливаем из URL, это само вызовет applyAllFilters
        restoreFromURL();
    });

    // Функция показа тоста
    function showToast(message, icon = 'bi-check-circle', isError = false) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `b2b-toast ${isError ? 'shake' : ''}`;
        toast.innerHTML = `<i class="bi ${icon}" style="${isError ? 'color:#e53e3e' : ''}"></i> <span>${message}</span>`;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            toast.style.transition = '0.4s';
            setTimeout(() => toast.remove(), 400);
        }, 4000); // 4 секунды видимости
    }

        // 1. AJAX добавление/изменение количества
        document.querySelectorAll('.ajax-cart-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const input = this.querySelector('.qty-field');
                const isInCart = this.classList.contains('is-in-cart');
                
                if (!isInCart && parseInt(input.value) === 0) {
                    input.value = 1;
                }
    
                const btn = this.querySelector('.btn-qty-apply');
                const icon = btn.querySelector('i');
                const originalIconClass = icon.className;
                const formData = new FormData(this);
                
                btn.disabled = true;
                icon.className = 'bi bi-hourglass-split';

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const input = form.querySelector('.qty-field');
                        const removeBtn = form.parentElement.querySelector('.btn-cart-remove');
                        const wasInCart = form.classList.contains('is-in-cart');
                        
                        input.setAttribute('data-original', data.total_qty);
                        input.value = data.total_qty;

                        if (data.action === 'removed') {
                            // 1. Сценарий: УДАЛЕНИЕ
                            form.classList.remove('is-in-cart');
                            input.setAttribute('data-original', 0);
                            input.value = 0;
                            if (removeBtn) removeBtn.classList.add('hidden-data');
                            showToast(`${data.product_name} <br><strong>удален из заказа</strong>`, 'bi-trash');
                        } else {
                            // 2. Сценарии: ДОБАВЛЕНИЕ или ОБНОВЛЕНИЕ
                            form.classList.add('is-in-cart');
                            if (removeBtn) removeBtn.classList.remove('hidden-data');

                            if (!wasInCart) {
                                // Товар только что залетел в корзину (был 0, стал > 0)
                                showToast(`<strong>Добавлено в заказ!</strong><br>${data.product_name} <br><strong>в количестве: ${data.total_qty} шт.</strong>`, 'bi-cart-plus');
                            } else {
                                // Товар уже был, просто поменяли цифру
                                showToast(`<strong>Количество обновлено</strong><br>${data.product_name} <br><strong>всего в заказе: ${data.total_qty} шт.</strong>`, 'bi-cart-check');
                            }
                        }
                        
                        form.classList.remove('needs-save');
                    } else {
                        showToast('Ошибка сервера', 'bi-exclamation-triangle', true);
                    }

                    icon.className = 'bi bi-check-lg';
                    if (window.updateTopbarCart && data.summary) window.updateTopbarCart(data.summary);
                    
                    setTimeout(() => { 
                        const isInCart = form.classList.contains('is-in-cart');
                        icon.className = isInCart ? 'bi bi-check2' : 'bi bi-cart-plus';
                        btn.disabled = true; 
                    }, 1500);
                })
                .catch(error => {
                    showToast('Ошибка сервера', 'bi-exclamation-triangle', true);
                    icon.className = originalIconClass;
                    btn.disabled = false;
            });
        });
    });

    // Функция удаления товара прямо из списка каталога
    function removeItemInCatalog(btnElement, productId) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('qty', 0);
            formData.append('_token', '{{ csrf_token() }}');
            btnElement.disabled = true;

            fetch('{{ route("cart.add") }}', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const container = btnElement.closest('.cart-controls-container');
                    const form = container.querySelector('.ajax-cart-form');
                    const input = form.querySelector('.qty-field');
                    form.classList.remove('is-in-cart', 'needs-save');
                    input.value = 0;
                    input.setAttribute('data-original', 0);
                    btnElement.classList.add('hidden-data');
                    form.querySelector('.btn-qty-apply i').className = 'bi bi-cart-plus';
                    showToast(`${data.product_name} <br><strong>удален из заказа</strong>`, 'bi-trash');
                    if (window.updateTopbarCart) window.updateTopbarCart(data.summary);
                }
            }).finally(() => { btnElement.disabled = false; });

    }

    // Удаляем загрузчик 
    window.jsLoaded = true; 
    setTimeout(() => {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.transition = 'opacity 0.3s ease';
            overlay.style.opacity = '0';
            
            setTimeout(() => overlay.remove(), 300);
        }
    }, 0); //задержка, если нужна 2сек=2000.

    // =========================================================
    // ЛОГИКА БЫСТРОГО ПРОСМОТРА + МАРКЕТИНГ (FINAL FIX)
    // =========================================================

    let currentProductGallery = [];
    let currentImageIndex = 0;

    function closeProductModal() {
        const modal = document.getElementById('productQuickView');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.body.removeAttribute('inert');
            closeZoomImage();
        }
    }

    // --- ЗУМ ---
    function openZoomImage() {
        const mainImg = document.getElementById('qv-main-img');
        if (!mainImg.src || mainImg.src.includes('noimage')) return;
        const overlay = document.getElementById('qv-zoom-overlay');
        const zoomImg = document.getElementById('qv-zoom-img');
        zoomImg.src = mainImg.src;
        overlay.classList.remove('anim-opacity');
        void overlay.offsetWidth; overlay.classList.add('anim-opacity');
        zoomImg.classList.remove('anim-fade');
        void zoomImg.offsetWidth; zoomImg.classList.add('anim-fade');
        overlay.style.display = 'flex';
        overlay.focus();
    }

    function closeZoomImage() {
        document.getElementById('qv-zoom-overlay').style.display = 'none';
    }

    // --- НАВИГАЦИЯ ---
    function navigateGallery(direction) {
        if (currentProductGallery.length <= 1) return;
        currentImageIndex += direction;
        if (currentImageIndex >= currentProductGallery.length) currentImageIndex = 0;
        else if (currentImageIndex < 0) currentImageIndex = currentProductGallery.length - 1;
        updateGalleryView();
    }

    function setGalleryIndex(index) {
        currentImageIndex = index;
        updateGalleryView();
    }

    function updateGalleryView() {
        const url = currentProductGallery[currentImageIndex];
        const mainImg = document.getElementById('qv-main-img');
        const zoomImg = document.getElementById('qv-zoom-img');
        mainImg.classList.add('img-switching');
        zoomImg.classList.add('img-switching');
        setTimeout(() => {
            mainImg.src = url;
            zoomImg.src = url;
            mainImg.classList.remove('img-switching');
            zoomImg.classList.remove('img-switching');
        }, 200); 
        document.querySelectorAll('.qv-thumb').forEach((thumb, idx) => {
            if (idx === currentImageIndex) {
                thumb.classList.add('active');
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                thumb.classList.remove('active');
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('productQuickView');
        if (modal && modal.style.display === 'flex') {
            if (e.key === 'ArrowLeft') navigateGallery(-1);
            if (e.key === 'ArrowRight') navigateGallery(1);
            if (e.key === 'Escape') document.getElementById('qv-zoom-overlay').style.display === 'flex' ? closeZoomImage() : closeProductModal();
        }
    });

    // Открытие товара по клику
    function openProductModal(id) {
        const cooldownTime = window.B2B_CONFIG?.delays?.short || 5000;
        const lastClick = parseInt(localStorage.getItem('qv_last_click_time') || 0);
        const now = Date.now();
        const timePassed = now - lastClick;

        if (timePassed < cooldownTime) {
            const secondsLeft = Math.ceil((cooldownTime - timePassed) / 1000);
            showToast(`Подождите ${secondsLeft} сек. перед открытием`, 'bi-hourglass-split', true);
            return; 
        }

        // Запоминаем время этого клика
        localStorage.setItem('qv_last_click_time', now);


        // 2. СТАНДАРТНАЯ ЛОГИКА ОТКРЫТИЯ
        const modal = document.getElementById('productQuickView');
        if (!modal) return;
        const NO_IMAGE = 'https://data.grifmaster.ru/files/dq9/data/noimage.png';

        // Сброс UI
        currentProductGallery = [];
        currentImageIndex = 0;
        const mainImg = document.getElementById('qv-main-img');
        if (mainImg) {
            mainImg.src = NO_IMAGE;
            mainImg.classList.remove('anim-fade');
        } 
        
        document.getElementById('qv-thumbs-list').innerHTML = ''; 
        document.getElementById('qv-name').innerText = 'Загрузка...';
        document.getElementById('qv-article').innerText = '...';
        document.getElementById('qv-price').innerText = '...';
        document.getElementById('qv-summary').innerHTML = '';
        document.getElementById('qv-features-list').innerHTML = '';
        document.getElementById('qv-stars-width').style.width = '0%';
        document.getElementById('qv-reviews').innerText = '';
        
        // Скрываем блоки
        document.getElementById('qv-site-link-container').style.display = 'none';
        document.getElementById('qv-zip-container').style.display = 'none';
        document.getElementById('qv-docs-block').style.display = 'none';
        document.getElementById('qv-logistics-block').style.display = 'none';
        
        document.querySelector('.qv-main-image-box').classList.remove('single-photo');
        document.getElementById('qv-zoom-overlay').classList.remove('single-photo');

        // Сброс кнопок в шапке
        const likeBtn = document.getElementById('qv-btn-like');
        const favBtn  = document.getElementById('qv-btn-fav');
        if(likeBtn) {
            likeBtn.className = 'qv-header-btn';
            likeBtn.innerHTML = '<i class="bi bi-heart"></i>';
            likeBtn.setAttribute('data-id', id);
        }
        if(favBtn) {
            favBtn.className = 'qv-header-btn';
            favBtn.innerHTML = '<i class="bi bi-star"></i>';
            favBtn.setAttribute('data-id', id);
        }

        const stockWrapper = document.getElementById('qv-stock-wrapper');
        const stockStatus = document.getElementById('qv-stock-status');
        if(stockWrapper) {
            stockWrapper.className = 'qv-stock'; 
            stockStatus.innerText = '...';
        }

        // ОТКРЫВАЕМ ОКНО (Только если прошли проверку времени)
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // 3. ЗАПРОС ДАННЫХ
        fetch(`/catalog/quick-view/${id}`, {
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest" // Обязательно для JSON ответа при ошибках
            }
        })
        .then(r => {
            // Если сервер все-таки вернул 429 (например, открыли в другой вкладке)
            if (r.status === 429) {
                return r.json().then(data => {
                    closeProductModal(); // Сразу закрываем окно обратно
                    showToast(data.message, 'error');
                    throw new Error('Throttled');
                });
            }
            if (!r.ok) throw new Error('Network error');
            return r.json();
        })
        .then(data => {
            if (!data.success) {
                showToast('Ошибка загрузки', 'bi-exclamation-triangle', true);
                closeProductModal();
                return;
            }

            // ЗАПОЛНЕНИЕ ДАННЫХ
            document.getElementById('qv-article').innerText = data.article || '-';
            document.getElementById('qv-name').innerText = data.name;
            document.getElementById('qv-price').innerText = data.price;

            if (stockWrapper) {
                const qty = parseFloat(data.stock_qty); 
                if (!isNaN(qty) && qty > 0) {
                    stockWrapper.className = 'qv-stock'; 
                    stockWrapper.querySelector('i').className = 'bi bi-check-circle-fill';
                    stockStatus.innerText = 'В наличии';
                } else {
                    stockWrapper.className = 'qv-stock out'; 
                    stockWrapper.querySelector('i').className = 'bi bi-x-circle-fill';
                    stockStatus.innerText = 'Нет в наличии';
                }
            }

            const rating = parseFloat(data.rating || 0);
            const starPercent = (rating / 5) * 100;
            document.getElementById('qv-stars-width').style.width = `${starPercent}%`;
            document.getElementById('qv-reviews').innerText = data.rating_count ? `(${data.rating_count} отз.)` : '';

            // Галерея
            const thumbsList = document.getElementById('qv-thumbs-list');
            const mainBox = document.querySelector('.qv-main-image-box');
            const zoomOverlay = document.getElementById('qv-zoom-overlay');
            currentProductGallery = (data.gallery && data.gallery.length > 0) ? data.gallery : (data.image ? [data.image] : []);
            if (currentProductGallery.length === 0) currentProductGallery = [NO_IMAGE];

            if (currentProductGallery.length <= 1) {
                mainBox.classList.add('single-photo');
                zoomOverlay.classList.add('single-photo');
            } else {
                mainBox.classList.remove('single-photo');
                zoomOverlay.classList.remove('single-photo');
            }

            currentProductGallery.forEach((imgUrl, index) => {
                const thumb = document.createElement('img');
                thumb.src = imgUrl;
                thumb.className = 'qv-thumb';
                thumb.onclick = () => setGalleryIndex(index);
                thumb.onerror = function() { this.style.display = 'none'; };
                thumbsList.appendChild(thumb);
            });
            setGalleryIndex(0);

            // Доп. инфо
            const zipContainer = document.getElementById('qv-zip-container');
            const zipBtn = document.getElementById('qv-download-zip');
            if (currentProductGallery.length > 0 && currentProductGallery[0] !== NO_IMAGE) {
                    zipContainer.style.display = 'block';
                    if (data.download_url) zipBtn.href = data.download_url;
            } else {
                zipContainer.style.display = 'none';
            }

            const siteLinkContainer = document.getElementById('qv-site-link-container');
            const siteLink = document.getElementById('qv-site-link');
            if (data.product_url) {
                siteLink.href = data.product_url;
                siteLinkContainer.style.display = 'block';
            } else {
                siteLinkContainer.style.display = 'none';
            }
            
            // Документы
            const docsBlock = document.getElementById('qv-docs-block');
            const docsContent = document.getElementById('qv-docs-content');
            if (data.documents && data.documents.length > 0) {
                docsBlock.style.display = 'block';
                docsContent.innerHTML = '';
                data.documents.forEach(doc => {
                    const a = document.createElement('a');
                    a.href = doc.url; a.target = '_blank'; a.className = 'qv-doc-item';
                    let icon = 'bi-file-earmark-text';
                    const ext = (doc.ext || '').toLowerCase();
                    if(ext === 'pdf') icon = 'bi-file-earmark-pdf';
                    if(['doc', 'docx'].includes(ext)) icon = 'bi-file-earmark-word';
                    a.innerHTML = `<i class="bi ${icon}"></i> <span>${doc.name}</span>`;
                    docsContent.appendChild(a);
                });
            } else {
                docsBlock.style.display = 'none';
            }

            // Логистика
            const logContainer = document.getElementById('qv-logistics-content');
            const logBlock = document.getElementById('qv-logistics-block');
            if (data.logistics && data.logistics.length > 0) {
                logBlock.style.display = 'block';
                logContainer.innerHTML = '';
                data.logistics.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'qv-log-item';
                    div.innerHTML = `<span class="qv-log-name">${item.name}:</span> <span class="qv-log-val">${item.value}</span>`;
                    logContainer.appendChild(div);
                });
            } else {
                logBlock.style.display = 'none';
            }
            
            // Описание
            const summaryDiv = document.getElementById('qv-summary');
            if (data.summary) summaryDiv.innerHTML = data.summary;
            else if (data.description) summaryDiv.innerHTML = data.description;
            else summaryDiv.innerHTML = '';

            const featContainer = document.getElementById('qv-features-list');
            featContainer.innerHTML = '';
            if (data.features && data.features.length > 0) {
                data.features.forEach(feat => {
                    const div = document.createElement('div');
                    div.className = 'qv-spec-item'; 
                    div.innerHTML = `<span class="qv-spec-name">${feat.name}</span><span class="qv-spec-val">${feat.value}</span>`;
                    featContainer.appendChild(div);
                });
            } else {
                featContainer.innerHTML = '<div style="color:#999; font-size:13px; grid-column: 1/-1;">Основные характеристики не указаны</div>';
            }

            // Синхронизация кнопок
            if (data.is_liked) {
                likeBtn.classList.add('is-liked');
                likeBtn.innerHTML = '<i class="bi bi-heart-fill"></i>';
            }
            if (data.is_in_wishlist) {
                favBtn.classList.add('is-faved');
                favBtn.innerHTML = '<i class="bi bi-star-fill"></i>';
            }
            
            // Проверка глобального таймера скачивания (ZIP)
            checkGlobalCooldown('qv-download-zip');
        })
        .catch(err => {
            // Если ошибка не Throttled (то есть не 429), выводим в консоль
            if (err.message !== 'Throttled') {
                console.error(err);
                showToast('Ошибка сети', 'bi-exclamation-triangle', true);
            }
            // Закрываем модалку при любой ошибке (чтобы не висел спиннер)
            // Но делаем это только если она была открыта (для случая Throttled она могла уже закрыться)
            const m = document.getElementById('productQuickView');
            if (m && m.style.display === 'flex') {
               // closeProductModal(); // Раскомментируйте, если хотите закрывать и при ошибке сети
            }
        });
    }

    // --- КЛИКЕРЫ ВНУТРИ МОДАЛКИ ---
    window.toggleLikeInModal = function() {
        const btn = document.getElementById('qv-btn-like');
        const prodId = btn.getAttribute('data-id');
        const tableBtn = document.querySelector(`.btn-like-${prodId}`);
        if(tableBtn) toggleLike(tableBtn, prodId);
    };

    window.toggleWishlistInModal = function() {
        const btn = document.getElementById('qv-btn-fav');
        const prodId = btn.getAttribute('data-id');
        const tableBtn = document.querySelector(`.btn-fav-${prodId}`);
        if(tableBtn) toggleWishlist(tableBtn, prodId);
    };


    // =========================================================
    // МАРКЕТИНГОВЫЕ ДЕЙСТВИЯ (AJAX)
    // =========================================================

    // 1. ЛАЙК (LIKE)
    function toggleLike(btn, id) {
        // --- 1. Client-Side защита (UX) ---
        const cooldownTime = 800; 
        const lastClick = parseInt(localStorage.getItem('like_last_click_time') || 0);
        const now = Date.now();
        const timePassed = now - lastClick;

        if (timePassed < cooldownTime) {
            // Если кликнул слишком быстро - просто игнорируем или показываем тост
            // (можно убрать showToast, если раздражает)
            return; 
        }
        localStorage.setItem('like_last_click_time', now);

        // --- 2. Запоминаем состояние ДО изменения (для отката) ---
        const wasActive = btn.classList.contains('is-active');
        const icon = btn.querySelector('i');
        const modalBtn = document.getElementById('qv-btn-like'); // Кнопка в модалке

        // --- 3. Оптимистичное обновление (меняем сразу) ---
        if (wasActive) {
            // Убираем лайк
            btn.classList.remove('is-active');
            icon.className = 'bi bi-heart';
            if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                modalBtn.classList.remove('is-liked');
                modalBtn.innerHTML = '<i class="bi bi-heart"></i>';
            }
        } else {
            // Ставим лайк
            btn.classList.add('is-active');
            icon.className = 'bi bi-heart-fill';
            if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                modalBtn.classList.add('is-liked');
                modalBtn.innerHTML = '<i class="bi bi-heart-fill"></i>';
            }
        }

        // --- 4. Отправка на сервер ---
        fetch(`/catalog/like-do/${id}`, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                'Content-Type': 'application/json', 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json' // Важно для получения текста ошибки 429
            }
        })
        .then(r => {
            // Если сервер вернул ошибку (429, 500, 401 и т.д.)
            if (!r.ok) {
                return r.json().then(err => { throw new Error(err.message || 'Ошибка сервера'); });
            }
            return r.json();
        })
        .then(data => {
            if (data.success) {
                // Все хорошо, обновляем счетчики
                if (data.active) showToast('Вам понравилось!', 'bi-heart-fill');
                else showToast('Вам больше не нравится', 'bi-heartbreak');
                
                const countSpan = btn.closest('.js-art')?.parentElement?.querySelector('.store-name .product-meta-row .meta-item[title="Лайки"]');
                if(countSpan) {
                    if (data.count > 0) {
                        countSpan.innerHTML = `<i class="bi bi-heart-fill" style="color: #e11d48; opacity: 0.7;"></i> ${data.count}`;
                        countSpan.style.display = 'inline-flex';
                    } else {
                        countSpan.style.display = 'none';
                    }
                }
            }
        })
        .catch(error => {
            // --- 5. ОТКАТ (ROLLBACK) ---
            // Если произошла ошибка (429 или сеть), возвращаем всё как было
            console.error(error);
            showToast(error.message, 'bi-exclamation-triangle', true); // Покажем "Слишком часто..."

            if (wasActive) {
                // Было активно -> мы убрали -> надо вернуть назад (добавить)
                btn.classList.add('is-active');
                icon.className = 'bi bi-heart-fill';
                if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                    modalBtn.classList.add('is-liked');
                    modalBtn.innerHTML = '<i class="bi bi-heart-fill"></i>';
                }
            } else {
                // Было неактивно -> мы добавили -> надо убрать
                btn.classList.remove('is-active');
                icon.className = 'bi bi-heart';
                if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                    modalBtn.classList.remove('is-liked');
                    modalBtn.innerHTML = '<i class="bi bi-heart"></i>';
                }
            }
        });
    }

    // 2. ИЗБРАННОЕ (WISHLIST)
    function toggleWishlist(btn, id) {
        // --- 1. Client-Side ---
        const cooldownTime = 800; 
        const lastClick = parseInt(localStorage.getItem('fav_last_click_time') || 0);
        const now = Date.now();
        const timePassed = now - lastClick;

        if (timePassed < cooldownTime) {
            return; 
        }
        localStorage.setItem('fav_last_click_time', now);

        // --- 2. Состояние ДО ---
        const wasActive = btn.classList.contains('is-active');
        const icon = btn.querySelector('i');
        const modalBtn = document.getElementById('qv-btn-fav');

        // --- 3. Оптимистичное UI ---
        if (wasActive) {
            btn.classList.remove('is-active');
            icon.className = 'bi bi-star';
            if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                modalBtn.classList.remove('is-faved');
                modalBtn.innerHTML = '<i class="bi bi-star"></i>';
            }
        } else {
            btn.classList.add('is-active');
            icon.className = 'bi bi-star-fill';
            // Эффект увеличения
            btn.style.transform = "scale(1.2)";
            setTimeout(() => btn.style.transform = "scale(1)", 200);
            
            if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                modalBtn.classList.add('is-faved');
                modalBtn.innerHTML = '<i class="bi bi-star-fill"></i>';
            }
        }

        // --- 4. Запрос ---
        fetch(`/catalog/wishlist-do/${id}`, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                'Content-Type': 'application/json', 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => {
            if (!r.ok) {
                return r.json().then(err => { throw new Error(err.message || 'Ошибка сервера'); });
            }
            return r.json();
        })
        .then(data => {
             if (data.success) {
                if(data.active) showToast('Добавлено в избранное!', 'bi-bookmark-fill');
                else showToast('Удалено из избранного', 'bi-bookmark-dash');
                
                const countSpan = btn.closest('.js-art')?.parentElement?.querySelector('.store-name .product-meta-row .meta-item[title="В избранном у других"]');
                if(countSpan) {
                    if (data.count > 0) {
                        countSpan.innerHTML = `<i class="bi bi-star-fill" style="color: #f59e0b; opacity: 0.7;"></i> ${data.count}`;
                        countSpan.style.display = 'inline-flex';
                    } else {
                        countSpan.style.display = 'none';
                    }
                }
            }
        })
        .catch(error => {
            // --- 5. ОТКАТ (ROLLBACK) ---
            console.error(error);
            showToast(error.message, 'bi-exclamation-triangle', true);

            if (wasActive) {
                // Возвращаем активность
                btn.classList.add('is-active');
                icon.className = 'bi bi-star-fill';
                if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                    modalBtn.classList.add('is-faved');
                    modalBtn.innerHTML = '<i class="bi bi-star-fill"></i>';
                }
            } else {
                // Убираем активность
                btn.classList.remove('is-active');
                icon.className = 'bi bi-star';
                if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                    modalBtn.classList.remove('is-faved');
                    modalBtn.innerHTML = '<i class="bi bi-star"></i>';
                }
            }
        });
    }
</script>
@endpush