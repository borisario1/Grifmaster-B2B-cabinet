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
            <button type="button" class="btn btn-secondary" onclick="closeModal('{{ $id }}')">Отмена</button>
            {{-- Добавляем текст таймера внутрь кнопки через span --}}
            <button type="button" class="btn {{ $btnClass ?? 'btn-primary' }} btn-modal-submit" id="{{ $id }}Submit">
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
let originalBtnText = '';

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = 'auto';
    if (modalTimer) clearInterval(modalTimer);
}

/**
 * @param id - ID модалки (universalConfirm)
 * @param callback - функция, которая выполнится при нажатии "Подтвердить"
 * @param title - (опционально) новый заголовок
 * @param message - (опционально) новый текст вопроса
 * @param delay - задержка в секундах (0 по умолчанию)
 * @param btnText - свое название кнопки, по умолчанию "Подтвердить"
 */
function openModal(id, callback, title = '', message = '', delay = 0, btnText = '') {
    const modal = document.getElementById(id);
    const submitBtn = document.getElementById(id + 'Submit');
    const textSpan = submitBtn.querySelector('.submit-text');

    if (title) modal.querySelector('h3').textContent = title;
    if (message) modal.querySelector('.custom-modal-body').textContent = message;

    // Устанавливаем текст кнопки: либо кастомный, либо дефолтный "Подтвердить"
    const finalBtnText = btnText || 'Подтвердить';
    textSpan.textContent = finalBtnText;
    originalBtnText = finalBtnText;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    if (modalTimer) clearInterval(modalTimer);
    submitBtn.disabled = false;

    if (delay > 0) {
        submitBtn.disabled = true;
        let timeLeft = delay;
        
        // Сразу показываем текст с таймером
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
        closeModal(id);
    };
}
</script>