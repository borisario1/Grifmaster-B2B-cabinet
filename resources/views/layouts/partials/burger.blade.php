<div class="burger-overlay" id="burgerMenu">
    <div class="burger-inner">
        
        {{-- 1. БЛОК ОРГАНИЗАЦИИ (Работает по настройке show_user_block) --}}
        @if(config('b2b_menu.burger_settings.show_user_block'))
            <div class="burger-user-section">
                @php
                    $user = Auth::user();
                    $orgCount = $user->organizations()->count();
                    $selectedOrg = $user->selected_org_id ? $user->organizations()->find($user->selected_org_id) : null;
                    
                    if ($orgCount === 0) {
                        $link = route('organizations.create');
                        $label = 'Создайте организацию';
                        $isAlert = true;
                    } elseif (!$selectedOrg) {
                        $link = route('organizations.index');
                        $label = 'Выберите организацию';
                        $isAlert = true;
                    } else {
                        $link = route('organizations.index');
                        $label = $selectedOrg->name;
                        $isAlert = false;
                    }
                @endphp

                <a href="{{ $link }}" class="burger-org-card {{ $isAlert ? 'is-empty' : '' }}">
                    <div class="org-card-icon">
                        <i class="bi {{ $isAlert ? 'bi-building-exclamation' : 'bi-building-check' }}"></i>
                    </div>
                    <div class="org-card-name">{{ $label }}</div>
                </a>
            </div>
        @endif

        {{-- 2. ОСНОВНЫЕ ПУНКТЫ СЕТКОЙ (Зависит от show_priority_block) --}}
        @if(config('b2b_menu.burger_settings.show_priority_block'))
            <div class="burger-section-title">Главное</div>
            <nav class="burger-main-grid">
                @foreach ($menu as $item)
                    @if (is_array($item) && isset($item['show_in']) && in_array('burger', $item['show_in']) && ($item['priority'] ?? '') === 'high')
                        @php
                            $currentPath = '/' . ltrim(request()->path(), '/');
                            $itemPath = '/' . ltrim(parse_url($item['url'], PHP_URL_PATH), '/');
                            
                            $isActive = ($currentPath === $itemPath) || (str_starts_with($currentPath, $itemPath . '/'));

                            if ($itemPath === '/store') {
                                if (str_contains($currentPath, '/store/order') || str_contains($currentPath, '/store/cart')) {
                                    $isActive = false;
                                }
                            }
                            
                            if ($itemPath === '/store/orders' && str_contains($currentPath, '/store/orders/')) {
                                $isActive = true;
                            }
                        @endphp
                        <a href="{{ $item['url'] }}" class="burger-grid-item {{ $isActive ? 'active' : '' }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span>{{ $item['title'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>
        @endif

        {{-- 3. СЕРВИСЫ (Список) --}}
        <div class="burger-section-title">Сервисы</div>
        <nav class="burger-secondary-nav">
            @foreach ($menu as $key => $item)
                @if (is_array($item) && isset($item['show_in']) && in_array('burger', $item['show_in']) && ($item['priority'] ?? '') !== 'high' && $item['url'] !== '/logout')
                    <a href="{{ $item['url'] }}" class="burger-secondary-link">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['title'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- 4. КОНТАКТЫ МЕНЕДЖЕРА --}}
        @if(config('b2b_menu.burger_settings.show_contact_info'))
            <div class="burger-contacts">
                <div class="contact-header">Отдел продаж</div>
                 @if(config('b2b_menu.burger_settings.manager_info'))<div class="contact-subtext">{{ config('b2b_menu.burger_settings.manager_info') }}</div>@endif
                
                <div class="contact-list">
                    {{-- Телефон --}}
                    <a href="tel:{{ str_replace([' ', '-', '(', ')'], '', config('b2b_menu.burger_settings.manager_phone')) }}" class="contact-item">
                        <i class="bi bi-telephone"></i> 
                        <span>{{ config('b2b_menu.burger_settings.manager_phone') }}</span>
                    </a>

                    {{-- Почта --}}
                    <a href="mailto:{{ config('b2b_menu.burger_settings.manager_email') }}" class="contact-item">
                        <i class="bi bi-envelope"></i> 
                        <span>{{ config('b2b_menu.burger_settings.manager_email') }}</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- 5. ВЫХОД (Берем данные из конфига) --}}
        <a href="#" class="burger-logout-link" onclick="event.preventDefault(); openModal('universalConfirm', () => { document.getElementById('logout-form').submit(); }, 'Выход из системы', 'Вы действительно хотите выйти?')">
            <i class="bi {{ $menu['logout']['icon'] ?? 'bi-box-arrow-right' }}"></i> 
            {{ $menu['logout']['title'] ?? 'Выйти из системы' }}
        </a>
    </div>
</div>

{{-- 6. TAB BAR --}}
@if(config('b2b_menu.mobile_tab_bar.enabled'))
    <div class="mobile-bottom-nav">
        @foreach(config('b2b_menu.mobile_tab_bar.items') as $tab)
            @php
                $isTabActive = request()->is(ltrim($tab['url'], '/').'*');
            @endphp
            <a href="{{ $tab['url'] }}" class="nav-tab {{ $isTabActive ? 'active' : '' }}">
                <div class="nav-tab-icon">
                    <i class="bi {{ $tab['icon'] }}"></i>
                    @if(isset($tab['is_cart']) && ($cartSummary['pos'] ?? 0) > 0)
                        <span class="nav-tab-badge"></span>
                    @endif
                </div>
                <span class="nav-tab-label">{{ $tab['title'] }}</span>
            </a>
        @endforeach
    </div>
@endif