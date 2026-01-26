/**
 * public/js/app-core.js
 * Основные системные скрипты: Модалки, Тосты, Хедер, Троттлинг.
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Инициализация Бургера
    const btn  = document.getElementById('burgerBtn');
    const menu = document.getElementById('burgerMenu');
    if (btn && menu) {
        btn.addEventListener('click', (e) => { e.stopPropagation(); menu.classList.toggle('open'); });
        document.addEventListener('click', (e) => { if (!menu.contains(e.target) && !btn.contains(e.target)) menu.classList.remove('open'); });
    }
});

// =========================================================
// 1. ТРОТТЛИНГ (Heavy Actions)
// =========================================================

function runHeavyAction(btn, loadingText = 'Подготовка...', customDelayMs = null) {
    if (btn.classList.contains('btn-blocked-wait')) return false;

    // Берем дефолт из конфига или 10 сек
    const defaultDelay = (window.B2B_CONFIG && window.B2B_CONFIG.defaultDelay) ? window.B2B_CONFIG.defaultDelay : 10000;
    const delay = customDelayMs ? customDelayMs : defaultDelay;
    const unlockTime = Date.now() + delay;

    localStorage.setItem('heavy_action_unlock_time', unlockTime);
    applyBlockState(btn, loadingText, unlockTime);
    return true; 
}

function checkGlobalCooldown(btnId) {
    const btn = document.getElementById(btnId);
    if (!btn) return;

    const unlockTime = parseInt(localStorage.getItem('heavy_action_unlock_time') || 0);
    const now = Date.now();

    if (unlockTime > now) {
        applyBlockState(btn, 'Ожидание...', unlockTime);
    }
}

function applyBlockState(btn, text, unlockTime) {
    if (btn.classList.contains('btn-blocked-wait')) return;

    if (!btn.getAttribute('data-original-html')) {
        btn.setAttribute('data-original-html', btn.innerHTML);
    }

    const originalWidth = btn.offsetWidth;
    btn.classList.add('btn-blocked-wait');
    if (originalWidth > 0) btn.style.width = `${originalWidth}px`;

    const initialSeconds = Math.ceil((unlockTime - Date.now()) / 1000);
    btn.innerHTML = `
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        <span class="js-timer-text" style="margin-left: 6px;">${text} (${initialSeconds})</span>
    `;

    const interval = setInterval(() => {
        const remaining = unlockTime - Date.now();
        
        if (remaining <= 0) {
            clearInterval(interval);
            btn.classList.remove('btn-blocked-wait');
            btn.innerHTML = btn.getAttribute('data-original-html'); 
            btn.style.width = '';
            localStorage.removeItem('heavy_action_unlock_time'); 
        } else {
            const seconds = Math.ceil(remaining / 1000);
            const textSpan = btn.querySelector('.js-timer-text');
            if (textSpan) {
                textSpan.innerText = `${text} (${seconds})`;
            }
        }
    }, 100); 
}

// =========================================================
// 2. КОРЗИНА (Topbar Cart)
// =========================================================

window.updateTopbarCart = function(summary) {
    const cartLink = document.getElementById('topbar-cart');
    const badgeContainer = document.getElementById('cart-qty-badge');
    const textContainer = document.getElementById('cart-text-info');

    if (!cartLink) return;

    if (summary && summary.pos > 0) {
        cartLink.classList.remove('cart-empty');
        cartLink.classList.add('cart-not-empty');
        if (badgeContainer) {
            badgeContainer.innerHTML = `<span class="cart-badge">${summary.qty}</span>`;
        }
        if (textContainer) {
            const amount = new Intl.NumberFormat('ru-RU').format(summary.amount);
            let word = 'позиций';
            const n = summary.pos % 100;
            const n1 = n % 10;
            if (n > 10 && n < 20) { word = 'позиций'; } 
            else if (n1 > 1 && n1 < 5) { word = 'позиции'; } 
            else if (n1 == 1) { word = 'позиция'; }
            textContainer.innerHTML = `
                <span class="cart-total-amount">${amount}&nbsp;₽</span>
                <span class="cart-total-label">${summary.pos} ${word}</span>
            `;
        }
    } else {
        cartLink.classList.remove('cart-not-empty');
        cartLink.classList.add('cart-empty');
        if (badgeContainer) badgeContainer.innerHTML = '';
        if (textContainer) textContainer.innerHTML = '';
    }
};

// =========================================================
// 3. ТОСТЫ (Notifications)
// =========================================================

function showToast(message, iconOrType = 'success', isError = false) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    
    // Логика определения иконки
    let iconClass = 'bi-check-circle'; // Дефолт
    
    if (iconOrType === 'success') {
        iconClass = 'bi-check-circle';
    } else if (iconOrType === 'error' || iconOrType === 'danger') {
        iconClass = 'bi-exclamation-triangle';
        isError = true; // Принудительно включаем режим ошибки
    } else {
        // Если передали не тип, а конкретный класс (например, 'bi-heart-fill')
        iconClass = iconOrType;
    }

    // Стили ошибки (красный цвет и тряска)
    const errorClass = isError ? 'shake' : '';
    const iconStyle = isError ? 'style="color:#e53e3e"' : '';

    toast.className = `b2b-toast ${errorClass}`;
    toast.innerHTML = `<i class="bi ${iconClass}" ${iconStyle}></i> <span>${message}</span>`;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        toast.style.transition = '0.4s';
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

// =========================================================
// 4. МОДАЛЬНЫЕ ОКНА (Классический вариант без inert)
// =========================================================

let modalTimer = null;

function closeModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    
    el.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    if (modalTimer) clearInterval(modalTimer);
}

function openModal(id, callback, title = '', message = '', delay = 0, btnText = '', isLoading = false) {
    const modal = document.getElementById(id);
    if (!modal) return;

    const submitBtn = document.getElementById(id + 'Submit');
    const cancelBtn = modal.querySelector('.btn-secondary');
    const modalIcon = modal.querySelector('.modal-icon');
    const textSpan = submitBtn.querySelector('.submit-text');
    const body = modal.querySelector('.custom-modal-body');

    // Просто блокируем скролл страницы
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Фокус для удобства (опционально)
    setTimeout(() => modal.focus(), 10);

    // Сброс UI
    submitBtn.style.display = 'inline-flex';
    cancelBtn.style.display = 'inline-flex';
    if(modalIcon) modalIcon.style.display = 'block';

    if (title) modal.querySelector('h3').textContent = title;
    if (message) body.textContent = message;

    // Режим лоадера
    if (isLoading) {
        submitBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
        if(modalIcon) modalIcon.style.display = 'none';
        body.innerHTML = `
            <div style="padding: 20px 0;">
                <div class="loader-spinner" style="width: 40px; height: 40px; border-width: 4px; border-top-color: #3295D1; margin: 0 auto 15px;"></div>
                <p>${message}</p>
            </div>
        `;
        return;
    }

    const finalBtnText = btnText || 'Подтвердить';
    textSpan.textContent = finalBtnText;

    if (modalTimer) clearInterval(modalTimer);
    submitBtn.disabled = false;

    // Таймер задержки кнопки
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

    // Навешиваем обработчик
    submitBtn.onclick = () => {
        callback();
        // Автозакрытие, если внутри callback не запустился лоадер
        if (!modal.querySelector('.loader-spinner')) {
            closeModal(id);
        }
    };
}