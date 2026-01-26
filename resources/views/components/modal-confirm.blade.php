{{-- 
    Универсальный компонент подтверждения действия
    Использование: <x-modal-confirm id="logoutModal" title="Выход" ... />
--}}
<div id="{{ $id }}" class="custom-modal-wrapper" style="display: none;" tabindex="-1">
    <div class="custom-modal-backdrop"></div>
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <i class="bi {{ $icon ?? 'bi-question-circle' }} modal-icon"></i>
            <h3>{{ $title }}</h3>
        </div>
        <div class="custom-modal-body">
            {{ $slot }}
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn-secondary btn-mid" onclick="closeModal('{{ $id }}')">Отмена</button>
            {{-- Добавляем текст таймера внутрь кнопки через span --}}
            <button type="button" class="{{ $btnClass ?? 'btn-primary' }} btn-mid btn-modal-submit" id="{{ $id }}Submit">
                <span class="submit-text">Подтвердить</span>
            </button>
        </div>
    </div>
</div>

{{-- 
    Стили модалки перенесены в forms.css
--}}

{{-- 
    resources/views/components/modal-confirm.blade.php
    Универсальный компонент подтверждения. Логика (JS) вынесена в app-core.js
--}}
<div id="{{ $id }}" class="custom-modal-wrapper" style="display: none;" tabindex="-1">
    <div class="custom-modal-backdrop"></div>
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <i class="bi {{ $icon ?? 'bi-question-circle' }} modal-icon"></i>
            <h3>{{ $title }}</h3>
        </div>
        <div class="custom-modal-body">
            {{ $slot }}
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn-secondary btn-mid" onclick="closeModal('{{ $id }}')">Отмена</button>
            <button type="button" class="{{ $btnClass ?? 'btn-primary' }} btn-mid btn-modal-submit" id="{{ $id }}Submit">
                <span class="submit-text">Подтвердить</span>
            </button>
        </div>
    </div>
</div>