@extends('layouts.app')

@section('title', 'Документы — ' . config('b2b.app_name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/files.css') }}">
@endpush

@section('content')
    <h1 class="page-title">Файловый центр</h1>
    
    {{-- БЛОК 1: ОБЩИЕ ДОКУМЕНТЫ (PINNED) --}}
    @if($pinnedResources->isNotEmpty())
        <section class="general-docs-section">
            <h2 class="section-title">Общие документы</h2>
            <div class="general-docs-grid">
                @foreach($pinnedResources as $resource)
                    <div class="general-doc-card">
                        <div class="doc-icon-large">
                            <i class="bi bi-file-earmark-{{ getIconType($resource->type) }}"></i>
                        </div>
                        <h3 class="doc-title-large">{{ $resource->title }}</h3>
                        
                        <div class="doc-meta-large">
                            @if(!$resource->external_link && $resource->file_path)
                                <span class="doc-size">{{ bytesToHuman($resource->getFileSize()) }}</span>
                            @endif
                            <span class="doc-updated">{{ $resource->updated_at->format('d.m.Y') }}</span>
                        </div>
                        
                        @if($resource->require_confirmation)
                            {{-- Требуется подтверждение (и для файлов, и для внешних ссылок) --}}
                            <button class="btn-download-large" 
                                    onclick="showConfirmModal({{ $resource->id }}, '{{ addslashes($resource->title) }}', `{!! addslashes($resource->confirmation_text) !!}`, '{{ addslashes($resource->confirm_btn_text) }}', {{ $resource->external_link ? 'true' : 'false' }})">
                                <i class="bi bi-{{ $resource->external_link ? 'box-arrow-up-right' : 'download' }}"></i> 
                                {{ $resource->confirm_btn_text ?: ($resource->external_link ? 'Открыть' : 'Скачать') }}
                            </button>
                        @elseif($resource->external_link)
                            {{-- Внешняя ссылка без подтверждения --}}
                            <a href="{{ $resource->external_link }}" 
                               class="btn-download-large"
                               target="_blank"
                               rel="noopener noreferrer">
                                <i class="bi bi-box-arrow-up-right"></i> Открыть
                            </a>
                        @else
                            {{-- Обычный файл без подтверждения --}}
                            <a href="{{ route('files.download', $resource->id) }}" 
                               class="btn-download-large">
                                <i class="bi bi-download"></i> Скачать
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif
    
    {{-- БЛОК 2: НАВИГАЦИЯ ПО БРЕНДАМ --}}
    @if($brands->isNotEmpty())
        <section class="brands-section">
            <h2 class="section-title">Бренды</h2>
            <div class="brands-grid">
                @foreach($brands as $brand)
                    <div class="brand-card" data-brand-id="{{ $brand->id }}" onclick="filterByBrand({{ $brand->id }})">
                        <img src="{{ asset($brand->logo_path) }}" alt="{{ $brand->name }}" class="brand-logo">
                        <div class="brand-info">
                            <h4 class="brand-name">{{ $brand->name }}</h4>
                            <span class="brand-docs-count">{{ $brand->resources_count }} {{ \Illuminate\Support\Str::plural('документ', $brand->resources_count, ['документ', 'документа', 'документов']) }}</span>
                        </div>
                    </div>
                @endforeach
                <div class="brand-card brand-card-all active" onclick="filterByBrand(null)">
                    <div class="brand-all-icon">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </div>
                    <div class="brand-info">
                        <h4 class="brand-name">Все бренды</h4>
                    </div>
                </div>
            </div>
        </section>
    @endif
    
    {{-- БЛОК 3: СПИСКИ ФАЙЛОВ ПО ТИПАМ --}}
    <section class="resources-section">
        <h2 class="section-title">Документы</h2>
        
        @foreach(['catalog' => 'Каталоги', 'certificate' => 'Сертификаты', '3d_model' => '3D модели', 'video' => 'Видео'] as $type => $typeLabel)
            @if(isset($resourcesByType[$type]) && $resourcesByType[$type]->isNotEmpty())
                <div class="resource-type-group">
                    <h3 class="resource-type-title">{{ $typeLabel }}</h3>
                    <div class="resources-grid">
                        @foreach($resourcesByType[$type] as $resource)
                            <div class="resource-card" data-brand-id="{{ $resource->brand_id ?? 'general' }}">
                                <div class="resource-icon">
                                    <i class="bi bi-file-earmark-{{ getIconType($resource->type) }}"></i>
                                </div>
                                <div class="resource-content">
                                    <h4 class="resource-title">{{ $resource->title }}</h4>
                                    
                                    @if($resource->brand)
                                        <span class="resource-brand">{{ $resource->brand->name }}</span>
                                    @endif
                                    
                                    <div class="resource-meta">
                                        @if(!$resource->external_link && $resource->file_path)
                                            <span class="meta-size">{{ bytesToHuman($resource->getFileSize()) }}</span>
                                        @endif
                                        <span class="meta-date">{{ $resource->updated_at->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                                
                                @if($resource->require_confirmation)
                                    {{-- Требуется подтверждение --}}
                                    <button class="btn-download-small" 
                                            onclick="showConfirmModal({{ $resource->id }}, '{{ addslashes($resource->title) }}', `{!! addslashes($resource->confirmation_text) !!}`, '{{ addslashes($resource->confirm_btn_text) }}', {{ $resource->external_link ? 'true' : 'false' }})"
                                            title="{{ $resource->external_link ? 'Открыть' : 'Скачать' }}">
                                        <i class="bi bi-{{ $resource->external_link ? 'box-arrow-up-right' : 'download' }}"></i>
                                    </button>
                                @elseif($resource->external_link)
                                    {{-- Внешняя ссылка без подтверждения --}}
                                    <a href="{{ $resource->external_link }}" 
                                       class="btn-download-small"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       title="Открыть">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @else
                                    {{-- Обычный файл без подтверждения --}}
                                    <a href="{{ route('files.download', $resource->id) }}" 
                                       class="btn-download-small">
                                        <i class="bi bi-download"></i>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </section>
    
    {{-- МОДАЛЬНОЕ ОКНО ПОДТВЕРЖДЕНИЯ --}}
    <x-modal-confirm 
        id="docConfirmModal" 
        title="Подтверждение скачивания" 
        icon="bi-file-earmark-arrow-down"
        btnClass="btn-primary"
    >
        <div id="docConfirmModalContent">
            <!-- Динамический контент -->
        </div>
    </x-modal-confirm>
@endsection

@push('scripts')
<script>
let currentBrandFilter = null;
let pendingDownloadUrl = null;

function filterByBrand(brandId) {
    currentBrandFilter = brandId;
    
    // Обновляем активный бренд
    document.querySelectorAll('.brand-card').forEach(card => {
        card.classList.remove('active');
    });
    
    if (brandId === null) {
        document.querySelector('.brand-card-all').classList.add('active');
    } else {
        document.querySelector(`.brand-card[data-brand-id="${brandId}"]`).classList.add('active');
    }
    
    // Фильтруем карточки
    document.querySelectorAll('.resource-card').forEach(card => {
        const cardBrandId = card.getAttribute('data-brand-id');
        
        if (brandId === null || cardBrandId === String(brandId) || cardBrandId === 'general') {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Скрываем пустые группы
    document.querySelectorAll('.resource-type-group').forEach(group => {
        const visibleCards = group.querySelectorAll('.resource-card:not([style*="display: none"])');
        group.style.display = visibleCards.length > 0 ? '' : 'none';
    });
}

function showConfirmModal(resourceId, title, confirmText, btnText, isExternal = false) {
    pendingDownloadUrl = `/files/download/${resourceId}?confirmed=1`;
    
    document.getElementById('docConfirmModalContent').innerHTML = confirmText;
    
    openModal('docConfirmModal', function() {
        if (isExternal) {
            window.open(pendingDownloadUrl, '_blank');
        } else {
            window.location.href = pendingDownloadUrl;
        }
    }, 'Подтверждение: ' + title, '', 0, btnText || (isExternal ? 'Открыть' : 'Скачать'));
}
</script>
@endpush
