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
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> → <span>Каталог товаров</span>
    </div>

    <h1 class="page-title">Каталог товаров</h1>

    <div class="store-filter-panel">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="store-filter-label">Живой поиск</label>
                <input type="text" class="search store-qty-input" placeholder="Артикул или название..." style="width: 100%;">
            </div>
            <div class="col-md-2">
                <label class="store-filter-label" style="margin-top: 10px;">Бренды</label>
                <div id="brand-tags-container" style="display: flex; flex-wrap: wrap; gap: 5px;">
                    @foreach($brands as $b)
                        <span class="brand-tag btn btn-secondary" 
                            data-brand="{{ $b }}" 
                            style="height: 32px;">{{ $b }}</span>
                    @endforeach
                </div>
            </div>
            <div class="col-md-2">
                <label class="store-filter-label" style="margin-top: 10px;">Коллекция</label>
                <select id="f-coll" class="store-qty-input" style="width: 100%;">
                    <option value="">Все коллекции</option>
                    @foreach($collections as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="store-filter-label" style="margin-top: 10px;">Категория</label>
                <select id="f-cat" class="store-qty-input" style="width: 100%;">
                    <option value="">Все категории</option>
                    @foreach($categories as $cat) <option value="{{ $cat }}">{{ $cat }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="store-filter-label" style="margin-top: 10px;">Тип товара</label>
                <select id="f-type" class="store-qty-input" style="width: 100%;">
                    <option value="">Все типы</option>
                    @foreach($types as $t) <option value="{{ $t }}">{{ $t }}</option> @endforeach
                </select>
            </div>
        </div>

        <div class="row g-3 mt-1 align-items-center">
            <div class="col-md-11">
                <div class="filter-checkbox-group">
                    <label><input type="checkbox" id="f-stock"> В наличии</label>
                    <label><input type="checkbox" id="f-no-coll"> Без коллекции</label>
                </div>
            </div>
            <div class="col-md-1 text-end">
                <button onclick="resetFilters()" class="btn-primary btn-big" style="margin-top: 23px;" title="Сбросить">
                    Очистить фильтр
                </button>
            </div>
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
                        <td><img src="{{ $i->image_url }}" class="store-img"></td>
                        <td class="js-art">{{ $i->article }}</td>
                        <td class="store-name">
                            <span class="js-name">{{ $i->name }}</span>
                            {{-- Скрытые данные для фильтрации --}}
                            <span class="js-coll hidden-data">{{ $i->collection ?? 'Без названия' }}</span>
                            <span class="js-cat hidden-data">{{ $i->product_category }}</span>
                            <span class="js-type hidden-data">{{ $i->product_type }}</span>
                        </td>
                        <td class="js-brand">{{ $i->brand }}</td>
                        <td class="js-stock" data-stock="{{ $i->free_stock }}">{{ $i->free_stock }}</td>
                        <td class="js-price" data-price="{{ $i->price }}">{!! str_replace(' ', '&nbsp;', number_format($i->price, 2, ',', ' ')) !!}&nbsp;₽</td>
                        <td>
                            @if ($i->discount_percent > 0)
                                <b>{!! str_replace(' ', '&nbsp;', number_format($i->partner_price, 2, ',', ' ')) !!}&nbsp;₽</b>
                                <div class="store-discount">скидка {{ (int)$i->discount_percent }}%</div>
                            @else
                                <span class="no-discount">—</span>
                            @endif
                        </td>
                        <td class="col-actions">
                            <div class="marketing-actions">
                                {{-- Быстрый просмотр --}}
                                <button type="button" class="btn-icon-action" 
                                        onclick="openProductModal({{ $i->id }})" 
                                        title="Быстрый просмотр">
                                    <i class="bi bi-eye"></i>
                                </button>
                                
                                {{-- Лайк (Избранное) --}}
                                {{-- Если товар уже лайкнут, сервер может вернуть класс (реализуем позже), пока механика клика --}}
                                <button type="button" class="btn-icon-action btn-like-{{ $i->id }}" 
                                        onclick="toggleLike(this, {{ $i->id }})" 
                                        title="В избранное">
                                    <i class="bi bi-heart"></i>
                                    {{-- Если хочешь выводить счетчик прямо в кнопке, раскомментируй строку ниже --}}
                                    {{-- <span class="like-counter" style="font-size: 10px; margin-left: 2px;">{{ $i->details->likes_count ?? '' }}</span> --}}
                                </button>
                            </div>

                            <div class="cart-controls-container">
                                @php $isInCart = $i->in_cart; @endphp
                                <form method="POST" action="{{ route('cart.add') }}" 
                                    class="ajax-cart-form catalog-qty-group {{ $isInCart ? 'is-in-cart' : '' }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $i->id }}">
                                    <input type="hidden" name="mode" value="set">
                                    
                                    <div class="cart-input-group">
                                        {{-- Минус --}}
                                        <button type="button" class="btn-qty-step" 
                                                onclick="handleMinus(this, {{ $i->id }}, '{{ $i->name }}')">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        
                                        <input type="number" name="qty" min="0" class="qty-field" 
                                            value="{{ $isInCart ? $i->cart_qty : 0 }}"
                                            data-original="{{ $i->in_cart ? $i->cart_qty : 0 }}" 
                                            oninput="handleInput(this)">
                                        
                                        {{-- Плюс --}}
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
        <i class="bi bi-search"></i> <span id="no-result-text">По вашему запросу ничего не найдено</span>
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

        // 1. ПАРАМЕТРЫ ЗАГРУЗКИ (Infinite Scroll / Показать еще)
        let itemsPerPage = 50; 
        const step = 50;

        const options = {
            valueNames: [
                'js-art', 'js-name', 'js-brand', 'js-coll', 'js-cat', 'js-type',
                { name: 'js-stock', attr: 'data-stock' },
                { name: 'js-price', attr: 'data-price' }
            ],
            page: itemsPerPage
        };

        const storeList = new List('store-app', options);

        // 2. ЭЛЕМЕНТЫ УПРАВЛЕНИЯ UI
        const loadMoreBtn = document.getElementById('load-more-btn');
        const showingInfo = document.getElementById('showing-info');
        const noResultMessage = document.getElementById('no-result-message');
        const noResultText = document.getElementById('no-result-text');

        function updateLoadMoreUI() {
            const totalItems = storeList.items.length; // Всего в базе
            const matched = storeList.matchingItems.length; // Найдено фильтром
            const shown = storeList.visibleItems.length; // Видно сейчас
            
            // 1. Обработка пустых состояний
            if (totalItems === 0) {
                noResultMessage.style.display = 'block';
                noResultText.innerText = "Каталог товаров в настоящее время пуст, попробуйте обновить страницу позже";
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
            
            // 2. Видимость кнопки "Показать еще"
            if (loadMoreBtn) {
                loadMoreBtn.style.display = (shown >= matched || matched === 0) ? 'none' : 'inline-block';
            }
        }

        // Кнопка теперь работает строго по клику (шаг 50)
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                itemsPerPage += step;
                storeList.show(1, itemsPerPage);
                updateLoadMoreUI();
                validateImages();
            });
        }

        // 3. ЛОГИКА ФИЛЬТРОВ
        const selectedBrands = new Set();
        const brandTags = document.querySelectorAll('.brand-tag');
        const selects = {
            coll: document.getElementById('f-coll'),
            cat: document.getElementById('f-cat'),
            type: document.getElementById('f-type')
        };
        const fStock = document.getElementById('f-stock');
        const fNoColl = document.getElementById('f-no-coll');

        function validateImages() {
            const images = document.querySelectorAll('.store-img');
            images.forEach(img => {
                img.onerror = function() { if (this.src !== NO_IMAGE) this.src = NO_IMAGE; };
                if (img.complete && img.naturalWidth === 0) img.src = NO_IMAGE;
                if (!img.getAttribute('src')) img.src = NO_IMAGE;
            });
        }

        // "Умное" обновление списков (не опустошает само себя)
        const updateSelectOptions = () => {
            const filterKeys = ['brand', 'coll', 'cat', 'type'];
            
            filterKeys.forEach(currentKey => {
                const otherActiveFilters = {};
                
                if (currentKey !== 'brand') {
                    if (selectedBrands.size > 0) otherActiveFilters.brand = selectedBrands;
                }
                
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
                            // Бренд доступен
                            tag.classList.remove('disabled');
                        } else {
                            // Бренд недоступен из-за других фильтров
                            tag.classList.add('disabled');
                            
                            // Если вдруг этот бренд был выбран, но теперь стал недоступен
                            // (например, сменили категорию), мы можем либо оставить его, 
                            // либо принудительно снять выбор. 
                            // Обычно лучше снять, чтобы фильтр был честным:
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

            // Сброс лимита до 50 при новой фильтрации
            itemsPerPage = 50;
            storeList.show(1, itemsPerPage);
            
            updateSelectOptions();
            updateLoadMoreUI();
            validateImages();
        };

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

        Object.values(selects).forEach(el => el.addEventListener('change', applyAllFilters));
        [fStock, fNoColl].forEach(el => el.addEventListener('change', applyAllFilters));

        storeList.on('searchComplete', updateLoadMoreUI);

        window.resetFilters = () => {
            selectedBrands.clear();
            brandTags.forEach(t => t.classList.replace('btn-primary', 'btn-secondary'));
            Object.values(selects).forEach(s => s.value = "");
            fStock.checked = false; 
            fNoColl.checked = false;
            const sInput = document.querySelector('.search');
            if(sInput) sInput.value = "";
            
            itemsPerPage = 50;
            storeList.search(); 
            storeList.filter();
            storeList.show(1, itemsPerPage);
            
            updateSelectOptions(); 
            updateLoadMoreUI();
            validateImages();
        };

        // Стартовый запуск
        updateSelectOptions();
        updateLoadMoreUI();
        validateImages();

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
        window.jsLoaded = true; 
        
        // Удаляем загрузчик 
        setTimeout(() => {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.style.transition = 'opacity 0.3s ease';
                overlay.style.opacity = '0';
                
                setTimeout(() => overlay.remove(), 300);
            }
        }, 2000);

    // =========================================================
    // ЛОГИКА БЫСТРОГО ПРОСМОТРА (FINAL + ZOOM ANIMATION)
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

    // --- ЗУМ (С АНИМАЦИЕЙ) ---
    function openZoomImage() {
        const mainImg = document.getElementById('qv-main-img');
        // Проверка: есть ли картинка и не заглушка ли это
        if (!mainImg.src || mainImg.src.includes('noimage')) return;
        
        const overlay = document.getElementById('qv-zoom-overlay');
        const zoomImg = document.getElementById('qv-zoom-img');
        
        // 1. Ставим картинку
        zoomImg.src = mainImg.src;
        
        // 2. Анимация ФОНА (через класс anim-opacity)
        overlay.classList.remove('anim-opacity');
        void overlay.offsetWidth; // Reflow
        overlay.classList.add('anim-opacity');
        
        // 3. Анимация КАРТИНКИ (через класс anim-fade)
        zoomImg.classList.remove('anim-fade');
        void zoomImg.offsetWidth; // Reflow
        zoomImg.classList.add('anim-fade');

        // 4. Показываем
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
        
        // 1. Начинаем анимацию исчезновения
        mainImg.classList.add('img-switching');
        zoomImg.classList.add('img-switching');

        // 2. Ждем совсем чуть-чуть (пока css отработает), меняем src и возвращаем вид
        setTimeout(() => {
            mainImg.src = url;
            zoomImg.src = url;

            // Возвращаем видимость (CSS сам сделает плавное появление)
            mainImg.classList.remove('img-switching');
            zoomImg.classList.remove('img-switching');
        }, 200); // 200мс задержка (чуть меньше чем transition в CSS, чтобы было динамично)

        // 3. Активная миниатюра (без задержки, мгновенно)
        document.querySelectorAll('.qv-thumb').forEach((thumb, idx) => {
            if (idx === currentImageIndex) {
                thumb.classList.add('active');
                thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                thumb.classList.remove('active');
            }
        });
    }

    // Клавиатура
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('productQuickView');
        if (modal && modal.style.display === 'flex') {
            if (e.key === 'ArrowLeft') navigateGallery(-1);
            if (e.key === 'ArrowRight') navigateGallery(1);
            if (e.key === 'Escape') {
                document.getElementById('qv-zoom-overlay').style.display === 'flex' ? closeZoomImage() : closeProductModal();
            }
        }
    });

    // --- ОТКРЫТИЕ МОДАЛКИ ---
    function openProductModal(id) {
        const modal = document.getElementById('productQuickView');
        if (!modal) return;

        const NO_IMAGE = 'https://data.grifmaster.ru/files/dq9/data/noimage.png';

        // --- 1. СБРОС СТАРЫХ ДАННЫХ ---
        currentProductGallery = [];
        currentImageIndex = 0;
        
        const mainImg = document.getElementById('qv-main-img');
        mainImg.src = NO_IMAGE;
        mainImg.classList.remove('anim-fade'); 

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
        document.getElementById('qv-logistics-block').style.display = 'none'; // <-- Сброс логистики
        
        document.querySelector('.qv-main-image-box').classList.remove('single-photo');
        document.getElementById('qv-zoom-overlay').classList.remove('single-photo');
        
        const stockWrapper = document.getElementById('qv-stock-wrapper');
        const stockStatus = document.getElementById('qv-stock-status');
        if(stockWrapper) {
            stockWrapper.className = 'qv-stock'; 
            stockStatus.innerText = '...';
        }

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // --- 2. ЗАПРОС ---
        fetch(`/catalog/quick-view/${id}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    showToast('Ошибка загрузки', 'bi-exclamation-triangle', true);
                    return;
                }

                document.getElementById('qv-article').innerText = data.article || '-';
                document.getElementById('qv-name').innerText = data.name;
                document.getElementById('qv-price').innerText = data.price;

                // Наличие
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

                // Рейтинг
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

                // Скрываем стрелки если 1 фото
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

                // Кнопки действий
                const zipContainer = document.getElementById('qv-zip-container');
                const zipBtn = document.getElementById('qv-download-zip');
                if (currentProductGallery.length > 0 && currentProductGallery[0] !== NO_IMAGE) {
                     zipContainer.style.display = 'block';
                     if (data.download_url) zipBtn.href = data.download_url;
                }
                
                const siteLinkContainer = document.getElementById('qv-site-link-container');
                const siteLink = document.getElementById('qv-site-link');
                if (data.product_url) {
                   siteLink.href = data.product_url;
                   siteLinkContainer.style.display = 'block';
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
                }

                // !!! ЛОГИСТИКА (Левая колонка) !!!
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
                }
                
                // Инфо (Описание)
                const summaryDiv = document.getElementById('qv-summary');
                if (data.summary) summaryDiv.innerHTML = data.summary;
                else if (data.description) summaryDiv.innerHTML = data.description;
                
                // Характеристики (Правая колонка - только основные)
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
            })
            .catch(err => {
                console.error(err);
                showToast('Ошибка сети', 'bi-exclamation-triangle', true);
            });
    }
</script>
@endpush