{{-- 
    Универсальный компонент подтверждения действия
    Использование: <x-modal-confirm id="logoutModal" title="Выход" ... />
--}}
<div id="{{ $id }}" class="custom-modal-wrapper" style="display: none;">
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

<style>
.custom-modal-wrapper {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    z-index: 9999;
    display: flex; align-items: center; justify-content: center;
}
.custom-modal-backdrop {
    position: absolute;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(8px); /* Тот самый блюр */
}
.custom-modal-content {
    position: relative;
    background: white;
    padding: 30px;
    border-radius: 15px;
    width: 100%; max-width: 400px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    animation: modalIn 0.3s ease-out;
}
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.timer-text {
    font-size: 0.85em;
    margin-left: 5px;
    opacity: 0.8;
}
.btn-modal-submit {
    min-width: 150px; /* Увеличим немного, чтобы точно влез длинный текст */
    display: inline-flex;
    align-items: center;
    justify-content: center; /* Центрируем текст по горизонтали */
    text-align: center;
    padding: 10px 20px; /* Симметричные отступы */
    position: relative;
    transition: all 0.3s ease;
}

/* Сбросим отступы у span, если они там остались */
.btn-modal-submit .submit-text {
    display: inline-block;
    width: 100%;
    margin: 0;
    padding: 0;
}
.btn-modal-submit:disabled {
    filter: grayscale(0.5);
    opacity: 0.8;
    cursor: not-allowed;
}

@keyframes modalIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.modal-icon { font-size: 3rem; color: #2c7be5; margin-bottom: 15px; display: block; }
.custom-modal-footer { margin-top: 25px; display: flex; gap: 10px; justify-content: center; }
</style>

<script>
let modalTimer = null;

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = 'auto';
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