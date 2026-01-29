@extends('layouts.app')

@section('title', 'Каталог товаров')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
@endpush

@section('content')
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
    {{-- РЯД 1: ПОИСК И БРЕНДЫ --}}
    <div class="row g-3">
        {{-- Поиск --}}
        <div class="col-lg-4 col-md-6 col-12">
            <label class="store-filter-label">Живой поиск</label>
            <input type="text" class="search store-qty-input" placeholder="Артикул или название..." style="width: 100%;">
        </div>
        
        {{-- Бренды --}}
        <div class="col-lg-8 col-md-6 col-12">
            <label class="store-filter-label">Бренды</label>
            <div id="brand-tags-container" style="display: flex; flex-wrap: wrap; gap: 5px;">
                @foreach($brands as $b)
                    <span class="brand-tag btn btn-secondary" 
                        data-brand="{{ $b }}" 
                        style="height: 32px;">{{ $b }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- РЯД 2: ТЕ САМЫЕ 3 ФИЛЬТРА В ОДНУ СТРОКУ (4+4+4 = 12) --}}
    {{-- mt-3 добавит отступ между рядами капсул --}}
    <div class="row g-3 mt-3">
        <div class="col-lg-4 col-md-4 col-12">
            <label class="store-filter-label">Коллекция</label>
            <select id="f-coll" class="store-qty-input" style="width: 100%;">
                <option value="">Все коллекции</option>
                @foreach($collections as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
            </select>
        </div>
        <div class="col-lg-4 col-md-4 col-12">
            <label class="store-filter-label">Категория</label>
            <select id="f-cat" class="store-qty-input" style="width: 100%;">
                <option value="">Все категории</option>
                @foreach($categories as $cat) <option value="{{ $cat }}">{{ $cat }}</option> @endforeach
            </select>
        </div>
        <div class="col-lg-4 col-md-4 col-12">
            <label class="store-filter-label">Тип товара</label>
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

    @if($products->isEmpty())
    <div class="empty-list-message">
        <i class="bi bi-info-circle"></i>
        <h4>
            @if(isset($isWishlist) && $isWishlist)
                В вашем списке отложенных товаров пока ничего нет.
            @elseif(isset($isLiked) && $isLiked)
                Вам еще ничего не понравилось.
            @elseif(isset($isOrdered) && $isOrdered)
                Вы еще ничего не заказали.
            @elseif(isset($isViewed) && $isViewed)
                Ваша история просмотренных товаров пока пуста.
            @else
                Список товаров пуст.
            @endif
        </h4>
    </div>
@else
    {{-- КОНТЕЙНЕР ТАБЛИЦЫ С ЛОАДЕРОМ --}}
    <div class="store-table-container" style="position: relative; min-height: 400px;">
        
        {{-- Слой лоадера: перекрывает ТОЛЬКО таблицу --}}
        <div id="table-loader" style="
            position: absolute; 
            top: 0; left: 0; right: 0; bottom: 0; 
            z-index: 10; 
            display: flex; 
            flex-direction: column;
            align-items: center; 
            justify-content: flex-start; {{-- Текст будет наверху --}}
            padding-top: 100px; {{-- Отступ сверху --}}
            background: rgba(255,255,255,0.7); 
            backdrop-filter: blur(4px);
            transition: opacity 0.3s ease;
        ">
            <div class="loader-spinner" style="width: 45px; height: 45px; border-top-color: #3295D1; margin-bottom: 20px;"></div>
            <span style="font-weight: 600; color: #0B466E; text-align: center; line-height: 1.6; text-shadow: 0 0 15px #fff;">
                Пожалуйста, подождите немного.<br>
                <small style="font-weight: 400; opacity: 0.8;">Обновляем товары, остатки и цены...</small>
            </span>
        </div>

        {{-- Сама таблица с начальной пульсацией --}}
        <div class="store-table-wrapper table-loading-pulse" id="main-table-content" 
             style="opacity: 0.6; filter: blur(4px); transition: all 0.4s ease;">
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
@endif
    
    {{-- Вставляем компонент модалки просмотра ЗДЕСЬ --}}
    <x-modal-product-view />

</div>
@endsection

@push('scripts')
{{-- 1. PRELOAD: Заставляем браузер начать качать тяжелую библиотеку сразу --}}
<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js" as="script">

{{-- 2. DEFER: Скрипт загрузится в фоне и сработает сразу после загрузки HTML --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js" defer></script>

<script>
    // Конфиг оставляем как есть, он легкий и нужен сразу
    window.StoreConfig = {
        csrf: "{{ csrf_token() }}",
        urls: {
            cartAdd: "{{ route('cart.add') }}",
            likeBase: "/catalog/like-do/", 
            wishlistBase: "/catalog/wishlist-do/",
            quickViewBase: "/catalog/quick-view/",
            recordViewBase: "/catalog/record-view/"
        },
        assets: {
            noImage: "https://data.grifmaster.ru/files/dq9/data/noimage.png"
        },
        settings: {
            itemsPerPage: 30,
            cooldownModal: {{ config('b2b.system.delays.short', 5000) * 1000 }} 
        }
    };
</script>

{{-- 3. DEFER: Ваш основной скрипт тоже грузим в фоне --}}
{{-- Добавляем ?v={{ time() }} временно, чтобы сбросить кэш у вас в браузере --}}
<script src="{{ asset('js/store-catalog.js') }}?v={{ time() }}" defer></script>
@endpush