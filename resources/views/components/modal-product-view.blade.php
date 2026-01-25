{{-- 
    Компонент: Модальное окно быстрого просмотра товара
    Файл: resources/views/components/modal-product-view.blade.php
--}}
<div id="productQuickView" class="custom-modal-wrapper" style="display: none;" tabindex="-1">
    <div class="custom-modal-backdrop" onclick="closeProductModal()"></div>
    
    <div class="custom-modal-content qv-modal-card">
        <button type="button" class="qv-close-btn" onclick="closeProductModal()">
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="qv-body">
            {{-- ЛЕВАЯ КОЛОНКА --}}
            <div class="qv-gallery-col">
                <div class="qv-main-image-box">
                    {{-- Стрелка ВЛЕВО --}}
                    <button class="qv-nav-btn prev" onclick="navigateGallery(-1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>

                    {{-- Картинка --}}
                    <img id="qv-main-img" src="" alt="Товар" onclick="openZoomImage()">
                    
                    {{-- Стрелка ВПРАВО --}}
                    <button class="qv-nav-btn next" onclick="navigateGallery(1)">
                        <i class="bi bi-chevron-right"></i>
                    </button>

                    <div id="qv-badge" class="qv-badge" style="display:none">Хит</div>
                    <div class="qv-zoom-hint"><i class="bi bi-arrows-fullscreen"></i></div>
                </div>
                
                <div id="qv-thumbs-list" class="qv-thumbs-row"></div>

                <div class="qv-left-actions">
                    {{-- Ссылка на сайт --}}
                    <div id="qv-site-link-container" style="display: none; margin-bottom: 8px;">
                        <a href="#" id="qv-site-link" target="_blank" class="btn-external-link full-width">
                            <i class="bi bi-box-arrow-up-right"></i> Смотреть на сайте
                        </a>
                    </div>

                    {{-- Скачивание ZIP --}}
                    <div id="qv-zip-container" style="display: none; margin-bottom: 15px;">
                        <a href="#" id="qv-download-zip" class="btn-link-action full-width">
                            <i class="bi bi-file-earmark-zip"></i> Скачать все фото (ZIP)
                        </a>
                    </div>

                    {{-- Документы --}}
                    <div id="qv-docs-block" class="qv-docs-wrapper" style="display: none;">
                        <div class="qv-docs-title">Документы:</div>
                        <div id="qv-docs-content" class="qv-docs-list"></div>
                    </div>

                    {{-- Логистика и упаковка --}}
                    <div id="qv-logistics-block" class="qv-logistics-wrapper" style="display: none;">
                        <div class="qv-docs-title">Логистика и упаковка:</div>
                        <div id="qv-logistics-content" class="qv-logistics-list"></div>
                    </div>
                </div>
            </div>

            {{-- ПРАВАЯ КОЛОНКА --}}
            <div class="qv-info-col">
                <div class="qv-header">
                    <div class="qv-sku">Артикул: <span id="qv-article">--</span></div>
                    <h2 id="qv-name">...</h2>
                    <div class="qv-rating-block">
                        <div class="stars-outer"><div class="stars-inner" id="qv-stars-width" style="width: 0%"></div></div>
                        <span class="qv-reviews-text" id="qv-reviews"></span>
                    </div>
                </div>

                <div class="qv-price-row">
                    <div class="qv-price" id="qv-price">0 ₽</div>
                    <div class="qv-stock" id="qv-stock-wrapper">
                        <i class="bi bi-check-circle-fill"></i> <span id="qv-stock-status">--</span>
                    </div>
                </div>

                <div id="qv-summary" class="qv-description-block"></div>

                <div class="qv-specs-block">
                    <h4 class="qv-section-title">Характеристики</h4>
                    <div id="qv-features-list" class="qv-specs-grid"></div>
                </div>
                
                <div class="qv-actions-footer">
                   <button class="btn-primary" onclick="closeProductModal()">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ЗУМ ОВЕРЛЕЙ --}}
    <div id="qv-zoom-overlay" class="qv-zoom-overlay" style="display: none;">
        <button class="qv-zoom-nav prev" onclick="navigateGallery(-1); event.stopPropagation();"><i class="bi bi-chevron-left"></i></button>
        <img id="qv-zoom-img" src="" alt="Zoom" onclick="event.stopPropagation();">
        <button class="qv-zoom-nav next" onclick="navigateGallery(1); event.stopPropagation();"><i class="bi bi-chevron-right"></i></button>
        <button class="qv-zoom-close" onclick="closeZoomImage()"><i class="bi bi-x-lg"></i></button>
        <div class="qv-zoom-bg-click" onclick="closeZoomImage()" style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:-1;"></div>
    </div>
</div>