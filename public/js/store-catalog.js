/**
 * store-catalog.js
 * Optimized & Defer ready
 */

// Глобальные переменные и функции
window.jsLoaded = false;

// 1. УТИЛИТЫ КОРЗИНЫ
window.handleInput = function(input) {
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

    if (isInCart && currentValue === originalValue) {
        applyBtn.disabled = true;
        form.classList.remove('needs-save');
        return;
    }

    applyBtn.disabled = false;
    form.classList.add('needs-save');

    if (applyIcon) {
        applyIcon.className = isInCart ? 'bi bi-check2' : 'bi bi-cart-plus';
    }
};

window.handleMinus = function(btn, productId, productName) {
    const input = btn.nextElementSibling;
    const currentValue = parseInt(input.value);
    const form = btn.closest('form');
    const isInCart = form.classList.contains('is-in-cart');

    if (currentValue > 1) {
        input.stepDown();
        handleInput(input);
    } else if (currentValue === 1) {
        if (isInCart) {
            const removeBtn = form.parentElement.querySelector('.btn-cart-remove');
            openModal('universalConfirm', () => { 
                removeItemInCatalog(removeBtn, productId);
            }, 'Удаление товара', `Вы уверены, что хотите удалить из заказа «${productName}»?`, 0, 'Да, удалить');
        } else {
            input.value = 0;
            handleInput(input);
        }
    }
};

window.removeItemInCatalog = function(btnElement, productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('qty', 0);
    formData.append('_token', window.StoreConfig.csrf);
    
    btnElement.disabled = true;

    fetch(window.StoreConfig.urls.cartAdd, { 
        method: 'POST', 
        body: formData, 
        headers: { 'X-Requested-With': 'XMLHttpRequest' } 
    })
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
            if (window.updateTopbarCart && data.summary) window.updateTopbarCart(data.summary);
        }
    }).finally(() => { btnElement.disabled = false; });
};

// =========================================================
// ОСНОВНАЯ ЛОГИКА (БЕЗ DOMContentLoaded, т.к. скрипт defer)
// =========================================================
(function() { // Оборачиваем в функцию, чтобы не засорять глобальную область
    const NO_IMAGE = window.StoreConfig.assets.noImage;

    // 1. ИНИЦИАЛИЗАЦИЯ LIST.JS
    let itemsPerPage = window.StoreConfig.settings.itemsPerPage; 
    const step = 30;

    const options = {
        valueNames: [
            'js-art', 'js-name', 'js-brand', 'js-coll', 'js-cat', 'js-type',
            { name: 'js-stock', attr: 'data-stock' },
            { name: 'js-price', attr: 'data-price' }
        ],
        page: itemsPerPage
    };

    // Это тяжелая операция, но необходимая для старта
    const storeList = new List('store-app', options);

    // =========================================================
    // 🔥 ЭФФЕКТ ЗАГРУЗКИ: Убираем лоадер и останавливаем пульсацию
    // =========================================================
    const tableLoader = document.getElementById('table-loader');
    const tableContent = document.getElementById('main-table-content');

    if (tableLoader && tableContent) {
        requestAnimationFrame(() => {
            // Скрываем слой лоадера
            tableLoader.style.opacity = '0';
            
            // Убираем размытие и пульсацию
            tableContent.classList.remove('table-loading-pulse');
            tableContent.style.opacity = '1';
            tableContent.style.filter = 'blur(0)';
            
            // Полностью удаляем лоадер через 300мс
            setTimeout(() => tableLoader.remove(), 300);
        });
    }

    // 2. ЭЛЕМЕНТЫ UI
    const loadMoreBtn = document.getElementById('load-more-btn');
    const showingInfo = document.getElementById('showing-info');
    const noResultMessage = document.getElementById('no-result-message');
    const noResultText = document.getElementById('no-result-text');
    
    // 3. ФИЛЬТРЫ
    const selectedBrands = new Set();
    const brandTags = document.querySelectorAll('.brand-tag');
    const selects = {
        coll: document.getElementById('f-coll'),
        cat: document.getElementById('f-cat'),
        type: document.getElementById('f-type')
    };
    const fStock = document.getElementById('f-stock');
    const fNoColl = document.getElementById('f-no-coll');

    // 4. URL SYNC
    let isRestoringState = false;

    const updateURL = () => {
        if (isRestoringState) return;

        const params = new URLSearchParams();
        const searchVal = document.querySelector('.search').value.trim();
        if (searchVal) params.set('q', searchVal);

        if (selectedBrands.size > 0) {
            params.set('brands', Array.from(selectedBrands).join(','));
        }

        if (selects.coll.value) params.set('coll', selects.coll.value);
        if (selects.cat.value)  params.set('cat', selects.cat.value);
        if (selects.type.value) params.set('type', selects.type.value);

        if (fStock.checked) params.set('stock', '1');
        if (fNoColl.checked) params.set('nocoll', '1');

        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.pushState({}, '', newUrl);
    };

    const restoreFromURL = () => {
        isRestoringState = true;
        const params = new URLSearchParams(window.location.search);

        // Если в URL есть поиск - List.js будет искать (это может занять время)
        if (params.has('q')) {
            const val = params.get('q');
            document.querySelector('.search').value = val;
            storeList.search(val);
        } else {
            // Очистка при пустом URL
            const sInput = document.querySelector('.search');
            if(sInput && sInput.value !== '') {
                sInput.value = '';
                storeList.search();
            }
        }

        selectedBrands.clear();
        brandTags.forEach(t => t.classList.replace('btn-primary', 'btn-secondary'));
        
        if (params.has('brands')) {
            const brandsFromUrl = params.get('brands').split(',');
            brandsFromUrl.forEach(b => {
                selectedBrands.add(b);
                const tag = document.querySelector(`.brand-tag[data-brand="${b}"]`);
                if (tag) tag.classList.replace('btn-secondary', 'btn-primary');
            });
        }

        if (params.has('coll')) selects.coll.value = params.get('coll'); else selects.coll.value = "";
        if (params.has('cat'))  selects.cat.value  = params.get('cat');  else selects.cat.value = "";
        if (params.has('type')) selects.type.value = params.get('type'); else selects.type.value = "";

        fStock.checked = params.has('stock');
        fNoColl.checked = params.has('nocoll');

        // Самая тяжелая функция (фильтрация + DOM + проверка картинок)
        applyAllFilters();
        isRestoringState = false;
    };

    function validateImages() {
        // Проверяем только видимые картинки внутри списка, чтобы не грузить процессор
        const images = document.querySelector('.list').querySelectorAll('.store-img');
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

        itemsPerPage = 30;
        storeList.show(1, itemsPerPage);
        
        updateSelectOptions();
        updateLoadMoreUI();
        validateImages();
        updateURL();
    };

    // Слушатели
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            itemsPerPage += step;
            storeList.show(1, itemsPerPage);
            updateLoadMoreUI();
            validateImages();
        });
    }

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
    
    const searchInput = document.querySelector('.search');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                updateURL();
            }, 800);
        });
    }

    window.resetFilters = () => {
        selectedBrands.clear();
        brandTags.forEach(t => t.classList.replace('btn-primary', 'btn-secondary'));
        Object.values(selects).forEach(s => s.value = "");
        fStock.checked = false; 
        fNoColl.checked = false;
        if(searchInput) searchInput.value = "";
        
        storeList.search(); 
        applyAllFilters();
    };

    window.addEventListener('popstate', function() {
        restoreFromURL();
    });

    // 7. AJAX КОРЗИНА
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
            .then(response => {
                // Если сервер вернул 429 (Бан или Слишком часто)
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message || 'Ошибка сервера'); });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const input = form.querySelector('.qty-field');
                    const removeBtn = form.parentElement.querySelector('.btn-cart-remove');
                    const wasInCart = form.classList.contains('is-in-cart');
                    
                    input.setAttribute('data-original', data.total_qty);
                    input.value = data.total_qty;

                    if (data.action === 'removed') {
                        form.classList.remove('is-in-cart');
                        input.setAttribute('data-original', 0);
                        input.value = 0;
                        if (removeBtn) removeBtn.classList.add('hidden-data');
                        showToast(`${data.product_name} <br><strong>удален из заказа</strong>`, 'bi-trash');
                    } else {
                        form.classList.add('is-in-cart');
                        if (removeBtn) removeBtn.classList.remove('hidden-data');

                        if (!wasInCart) {
                            showToast(`<strong>Добавлено в заказ!</strong><br>${data.product_name} <br><strong>в количестве: ${data.total_qty} шт.</strong>`, 'bi-cart-plus-fill');
                        } else {
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
                showToast(error.message, 'bi-exclamation-triangle', true);
                icon.className = originalIconClass;
                btn.disabled = false;
            });
        });
    });

    // =========================================================
    // 🔥 ФИНАЛЬНЫЙ ТРЮК: ОТКЛАДЫВАЕМ ТЯЖЕЛЫЕ ФИЛЬТРЫ
    // =========================================================
    // Мы запускаем restoreFromURL через setTimeout.
    // Это дает браузеру "передышку", чтобы успеть отрисовать скрытие спиннера (opacity: 0)
    // ДО того, как процессор загрузится фильтрацией.
    setTimeout(() => {
        restoreFromURL();
    }, 10);

})(); // Конец IIFE

// =========================================================
// БЫСТРЫЙ ПРОСМОТР И МАРКЕТИНГ (Original)
// =========================================================

let currentProductGallery = [];
let currentImageIndex = 0;

window.closeProductModal = function() {
    const modal = document.getElementById('productQuickView');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        closeZoomImage();
    }
};

window.openZoomImage = function() {
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
};

window.closeZoomImage = function() {
    document.getElementById('qv-zoom-overlay').style.display = 'none';
};

window.navigateGallery = function(direction) {
    if (currentProductGallery.length <= 1) return;
    currentImageIndex += direction;
    if (currentImageIndex >= currentProductGallery.length) currentImageIndex = 0;
    else if (currentImageIndex < 0) currentImageIndex = currentProductGallery.length - 1;
    updateGalleryView();
};

window.setGalleryIndex = function(index) {
    currentImageIndex = index;
    updateGalleryView();
};

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

window.openProductModal = function(id) {
    const cooldownTime = window.StoreConfig.settings.cooldownModal || 5000;
    const lastClick = parseInt(localStorage.getItem('qv_last_click_time') || 0);
    const now = Date.now();
    const timePassed = now - lastClick;

    if (timePassed < cooldownTime) {
        const secondsLeft = Math.ceil((cooldownTime - timePassed) / 1000);
        showToast(`Подождите ${secondsLeft} сек. перед открытием`, 'bi-hourglass-split', true);
        return; 
    }

    localStorage.setItem('qv_last_click_time', now);

    const modal = document.getElementById('productQuickView');
    if (!modal) return;
    const NO_IMAGE = window.StoreConfig.assets.noImage;

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
    
    document.getElementById('qv-site-link-container').style.display = 'none';
    document.getElementById('qv-zip-container').style.display = 'none';
    document.getElementById('qv-docs-block').style.display = 'none';
    document.getElementById('qv-logistics-block').style.display = 'none';
    
    document.querySelector('.qv-main-image-box').classList.remove('single-photo');
    document.getElementById('qv-zoom-overlay').classList.remove('single-photo');

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

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    fetch(window.StoreConfig.urls.quickViewBase + id, {
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(r => {
        if (r.status === 429) {
            return r.json().then(data => {
                closeProductModal(); 
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

        // ZIP
        const zipContainer = document.getElementById('qv-zip-container');
        const zipBtn = document.getElementById('qv-download-zip');
        if (currentProductGallery.length > 0 && currentProductGallery[0] !== NO_IMAGE) {
                zipContainer.style.display = 'block';
                if (data.download_url) zipBtn.href = data.download_url;
        } else {
            zipContainer.style.display = 'none';
        }

        // Ссылки
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

        if (data.is_liked) {
            likeBtn.classList.add('is-liked');
            likeBtn.innerHTML = '<i class="bi bi-heart-fill"></i>';
        }
        if (data.is_in_wishlist) {
            favBtn.classList.add('is-faved');
            favBtn.innerHTML = '<i class="bi bi-star-fill"></i>';
        }
        
        if (typeof checkGlobalCooldown === 'function') {
            checkGlobalCooldown('qv-download-zip');
        }
    })
    .catch(err => {
        if (err.message !== 'Throttled') {
            console.error(err);
            showToast('Ошибка сети', 'bi-exclamation-triangle', true);
        }
    });
};

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

// ЛАЙК
window.toggleLike = function(btn, id) {
    const cooldownTime = window.StoreConfig.settings.cooldownLike || 800; 
    const lastClick = parseInt(localStorage.getItem('like_last_click_time') || 0);
    const now = Date.now();
    const timePassed = now - lastClick;

    if (timePassed < cooldownTime) {
        const secondsLeft = Math.ceil((cooldownTime - timePassed) / 1000);
        showToast(`Подождите ${secondsLeft} сек.`, 'bi-hourglass-split', true);
        return; 
    }
    localStorage.setItem('like_last_click_time', now);

    const wasActive = btn.classList.contains('is-active');
    const icon = btn.querySelector('i');
    const modalBtn = document.getElementById('qv-btn-like');

    if (wasActive) {
        btn.classList.remove('is-active');
        icon.className = 'bi bi-heart';
        if(modalBtn && modalBtn.getAttribute('data-id') == id) {
            modalBtn.classList.remove('is-liked');
            modalBtn.innerHTML = '<i class="bi bi-heart"></i>';
        }
    } else {
        btn.classList.add('is-active');
        icon.className = 'bi bi-heart-fill';
        if(modalBtn && modalBtn.getAttribute('data-id') == id) {
            modalBtn.classList.add('is-liked');
            modalBtn.innerHTML = '<i class="bi bi-heart-fill"></i>';
        }
    }

    fetch(window.StoreConfig.urls.likeBase + id, {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': window.StoreConfig.csrf, 
            'Content-Type': 'application/json', 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json' 
        }
    })
    .then(r => {
        if (!r.ok) return r.json().then(err => { throw new Error(err.message || 'Ошибка сервера'); });
        return r.json();
    })
    .then(data => {
        if (data.success) {
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
        console.error(error);
        showToast(error.message, 'bi-exclamation-triangle', true);

        if (wasActive) {
            btn.classList.add('is-active');
            icon.className = 'bi bi-heart-fill';
            if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                modalBtn.classList.add('is-liked');
                modalBtn.innerHTML = '<i class="bi bi-heart-fill"></i>';
            }
        } else {
            btn.classList.remove('is-active');
            icon.className = 'bi bi-heart';
            if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                modalBtn.classList.remove('is-liked');
                modalBtn.innerHTML = '<i class="bi bi-heart"></i>';
            }
        }
    });
};

// ИЗБРАННОЕ
window.toggleWishlist = function(btn, id) {
    const cooldownTime = window.StoreConfig.settings.cooldownLike || 800; 
    const lastClick = parseInt(localStorage.getItem('fav_last_click_time') || 0);
    const now = Date.now();
    const timePassed = now - lastClick;

    if (timePassed < cooldownTime) {
        const secondsLeft = Math.ceil((cooldownTime - timePassed) / 1000);
        showToast(`Подождите ${secondsLeft} сек.`, 'bi-hourglass-split', true);
        return; 
    }
    localStorage.setItem('fav_last_click_time', now);

    const wasActive = btn.classList.contains('is-active');
    const icon = btn.querySelector('i');
    const modalBtn = document.getElementById('qv-btn-fav');

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
        btn.style.transform = "scale(1.2)";
        setTimeout(() => btn.style.transform = "scale(1)", 200);
        
        if(modalBtn && modalBtn.getAttribute('data-id') == id) {
            modalBtn.classList.add('is-faved');
            modalBtn.innerHTML = '<i class="bi bi-star-fill"></i>';
        }
    }

    fetch(window.StoreConfig.urls.wishlistBase + id, {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': window.StoreConfig.csrf, 
            'Content-Type': 'application/json', 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (!r.ok) return r.json().then(err => { throw new Error(err.message || 'Ошибка сервера'); });
        return r.json();
    })
    .then(data => {
         if (data.success) {
            if(data.active) showToast('Добавлено в избранное', 'bi-bookmark-check-fill');
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
        console.error(error);
        showToast(error.message, 'bi-exclamation-triangle', true);

        if (wasActive) {
            btn.classList.add('is-active');
            icon.className = 'bi bi-star-fill';
            if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                modalBtn.classList.add('is-faved');
                modalBtn.innerHTML = '<i class="bi bi-star-fill"></i>';
            }
        } else {
            btn.classList.remove('is-active');
            icon.className = 'bi bi-star';
            if(modalBtn && modalBtn.getAttribute('data-id') == id) {
                modalBtn.classList.remove('is-faved');
                modalBtn.innerHTML = '<i class="bi bi-star"></i>';
            }
        }
    });
};