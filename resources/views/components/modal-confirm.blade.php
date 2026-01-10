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

<script>
let modalTimer = null;

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Снимаем блокировку с основного контента
    document.querySelectorAll('body > *:not(script):not(.custom-modal-wrapper):not(.toast-container)').forEach(el => {
        el.removeAttribute('inert');
    });
    //const mainContent = document.querySelector('main') || document.getElementById('store-app');
    //if (mainContent) mainContent.removeAttribute('inert');

    if (modalTimer) clearInterval(modalTimer);
}

/**
 * @param id - ID модалки
 * @param callback - функция при нажатии
 * @param title - заголовок
 * @param message - текст
 * @param delay - задержка (сек)
 * @param btnText - текст кнопки
 * @param isLoading - если true, показывает лоадер и скрывает кнопки [NEW]
 */
function openModal(id, callback, title = '', message = '', delay = 0, btnText = '', isLoading = false) {
    const modal = document.getElementById(id);
    const submitBtn = document.getElementById(id + 'Submit');
    const cancelBtn = modal.querySelector('.btn-secondary');
    const modalIcon = modal.querySelector('.modal-icon');
    const textSpan = submitBtn.querySelector('.submit-text');
    const body = modal.querySelector('.custom-modal-body');

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Блокируем всё, что ВНЕ модалки для клавиатуры и мыши
    // Атрибут inert делает элементы нефокусируемыми и некликабельными
    document.querySelectorAll('body > *:not(script):not(.custom-modal-wrapper):not(.toast-container)').forEach(el => {
        el.setAttribute('inert', 'true');
    });

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    //const mainContent = document.querySelector('main') || document.getElementById('store-app');
    //if (mainContent) mainContent.setAttribute('inert', 'true');

    // Автофокус на кнопку отмены или саму модалку, чтобы сбить фокус с кнопок в таблице
    setTimeout(() => modal.focus(), 10);

    // Сбрасываем видимость (на случай если открываем после лоадера)
    submitBtn.style.display = 'inline-flex';
    cancelBtn.style.display = 'inline-flex';
    modalIcon.style.display = 'block';

    if (title) modal.querySelector('h3').textContent = title;
    if (message) body.textContent = message;

    // Режим лоадера (для оформления заказа)
    if (isLoading) {
        submitBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
        modalIcon.style.display = 'none';
        body.innerHTML = `
            <div style="padding: 20px 0;">
                <div class="loader-spinner" style="width: 40px; height: 40px; border-width: 4px; border-top-color: #3295D1; margin: 0 auto 15px;"></div>
                <p>${message}</p>
            </div>
        `;
        modal.style.display = 'flex';
        return; // Прекращаем выполнение, кнопки не нужны
    }

    const finalBtnText = btnText || 'Подтвердить';
    textSpan.textContent = finalBtnText;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    if (modalTimer) clearInterval(modalTimer);
    submitBtn.disabled = false;

    if (delay > 0) {
        submitBtn.disabled = true;
        let timeLeft = delay;
        textSpan.textContent = `${finalBtnText} (${timeLeft})`;
        modalTimer = setInterval(() => {
            timeLeft--;
            if (timeLeft > 0) {
                textSpan.textContent = `${finalBtnText} (${timeLeft})`;
            } else {
                clearInterval(modalTimer);
                submitBtn.disabled = false;
                textSpan.textContent = finalBtnText;
            }
        }, 1000);
    }

    submitBtn.onclick = () => {
            callback();
            if (!modal.querySelector('.loader-spinner')) {
                closeModal(id);
            }
        };
}
</script>