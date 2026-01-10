<header class="topbar">
    <div class="topbar-inner">
        <div class="topbar-left">
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset(config('b2b.branding.logo_path')) }}" alt="{{ config('b2b.app_name') }}" class="topbar-logo">
            </a>
            <span class="topbar-title">{{ config('b2b.app_name') }}</span>
        </div>
        
        <div class="topbar-right">
            <a href="{{ route('cart.index') }}" 
            class="topbar-cart-link {{ ($cartSummary['pos'] ?? 0) > 0 ? 'cart-not-empty' : '' }}" 
            title="Корзина" 
            id="topbar-cart">
                
                <div class="topbar-cart-icon-wrapper">
                    <i class="bi bi-basket3"></i>
                    <span id="cart-qty-badge">
                        @if(($cartSummary['pos'] ?? 0) > 0)
                            <span class="cart-badge">{{ $cartSummary['qty'] }}</span>
                        @endif
                    </span>
                </div>

                <div class="topbar-cart-details" id="cart-text-info">
                    @if(($cartSummary['pos'] ?? 0) > 0)
                        <span class="cart-total-amount">{{ number_format($cartSummary['amount'], 0, '.', ' ') }}&nbsp;₽</span>
                        <span class="cart-total-label">
                            {{ trans_choice('{1} :count позиция|[2,4] :count позиции|[5,*] :count позиций', $cartSummary['pos'], ['count' => $cartSummary['pos']], 'ru') }}
                        </span>
                    @endif
                </div>
            </a>

            <a href="{{ route('notifications.index') }}" class="topbar-icon topbar-notify" title="Уведомления">
                <i class="bi bi-bell-fill"></i>
                @if(($unreadNotificationsCount ?? 0) > 0)
                    <span class="notify-badge">{{ $unreadNotificationsCount }}</span>
                @endif
            </a>

            <a href="{{ route('profile.edit') }}" class="topbar-icon" title="Мой профиль">
                <i class="bi bi-person-circle"></i>
            </a>

            <a href="#" 
               class="topbar-icon" 
               title="Выйти"
               onclick="event.preventDefault(); openModal('universalConfirm', () => { document.getElementById('logout-form').submit(); }, 'Выход из системы', 'Вы действительно хотите выйти?')">
                <i class="bi bi-box-arrow-right"></i>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>

            <button class="topbar-burger" id="burgerBtn">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>
</header>