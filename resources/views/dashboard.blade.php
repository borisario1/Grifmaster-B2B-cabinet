@extends('layouts.app')

@section('title', 'Дашборд — ' . config('b2b.app_name'))

@section('content')

    @php
        $user = Auth::user();
        $profile = $user->profile;

        $roleName = match($user->role) {
            'admin'   => 'Администратор',
            'manager' => 'Менеджер',
            default   => 'Партнёр',
        };
    @endphp

    {{-- Уведомление о статусе (например, о необходимости выбрать организацию) --}}
    @if(isset($org_status) && $org_status['state'] !== 'selected')
        <div class="cart-alert-panel" style="margin-top: 20px; margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div class="cart-alert-icon"><i class="bi bi-info-circle"></i></div>
                <div style="font-size: 15px; color: #001F33;">
                    {!! $org_status['text'] !!}
                </div>
            </div>
        </div>
    @endif

    {{-- ЗАГОЛОВОК --}}
    <h1 class="page-title">
        Здравствуйте, {{ $profile->first_name ?? 'Уважаемый' }} {{ $profile->middle_name ?? 'партнёр' }}!
    </h1>

    {{-- ИНФО-БЛОК --}}
    <div class="card-info" style="padding: 18px 22px; border-radius: var(--min_radius);">
        <div style="display: flex; flex-direction: column; gap: 12px;">
            {{-- Верхняя строка --}}
            <div style="font-size: 15px; color: #666; display: flex; flex-wrap: wrap; column-gap: 25px; row-gap: 8px; align-items: center;">
                <div>Последний вход: <strong style="color: #001F33; font-weight: 500;">{{ $lastLoginText }}</strong></div>
                <div>Ваш статус: <strong style="color: #001F33; font-weight: 500;">{{ $roleName }}</strong></div>
            @if(!$currentOrg)
                <div style="font-size: 15px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-exclamation-triangle-fill" style="color: #e53e3e; font-size: 16px;"></i>
                    <span style="color: #e53e3e; font-weight: 500;">
                        {{ (isset($org_status['state']) && $org_status['state'] === 'no_org') ? 'Вы еще не создали организацию' : 'Организация не выбрана' }}
                    </span>
                </div>
            @endif
            </div>

            @if($currentOrg)
                <div style="height: 1px; background: rgba(0,0,0,0.06); margin: 4px 0;"></div>
                <div style="font-size: 16px; color: #666; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                    <span>Выбрана организация:</span>
                    <strong style="color: #001F33; font-weight: 500;">
                        {{ $currentOrg->name }}, ИНН: {{ $currentOrg->inn }}
                        @if($currentOrg->type !== 'ip' && $currentOrg->kpp)
                            , КПП: {{ $currentOrg->kpp }}
                        @endif
                    </strong>
                </div>
            @endif
        </div>
    </div>

    {{-- 
        =========================================================
        СЛАЙДЕР БАННЕРОВ
        ========================================================= 
    --}}
    @php
        // Берем массив баннеров. Если его нет, берем старый одиночный banner и оборачиваем в массив
        $banners = config('b2b_menu.banners', []);
        
        // Обратная совместимость, если в конфиге остался старый формат
        if (empty($banners) && config('b2b_menu.banner')) {
            $banners = [config('b2b_menu.banner')];
        }
    @endphp

    @if(!empty($banners))
        <div class="dashboard-banner-wrapper" id="promoSlider">
            
            {{-- Слайды --}}
            @foreach($banners as $index => $banner)
                <a href="{{ $banner['url'] ?? '#' }}" 
                   class="banner-slide {{ $index === 0 ? 'active' : '' }}" 
                   target="{{ $banner['target'] ?? '_self' }}">
                    <img src="{{ Str::startsWith($banner['image'], ['http', '/']) ? asset($banner['image']) : asset($banner['image']) }}" 
                         alt="{{ $banner['alt'] ?? 'Promo' }}">
                </a>
            @endforeach

            {{-- Точки навигации (показываем только если баннеров > 1) --}}
            @if(count($banners) > 1)
                <div class="banner-dots">
                    @foreach($banners as $index => $banner)
                        <div class="banner-dot {{ $index === 0 ? 'active' : '' }}" 
                             onclick="setBannerSlide({{ $index }})"></div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Скрипт слайдера --}}
        @if(count($banners) > 1)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let currentSlide = 0;
                const slides = document.querySelectorAll('#promoSlider .banner-slide');
                const dots = document.querySelectorAll('#promoSlider .banner-dot');
                const totalSlides = slides.length;
                let slideInterval;

                // Функция смены слайда
                window.setBannerSlide = function(index) {
                    // Сбрасываем текущие
                    slides[currentSlide].classList.remove('active');
                    if(dots.length) dots[currentSlide].classList.remove('active');

                    // Устанавливаем новые
                    currentSlide = index;
                    slides[currentSlide].classList.add('active');
                    if(dots.length) dots[currentSlide].classList.add('active');
                    
                    // Перезапускаем таймер, чтобы не переключилось сразу после клика
                    resetTimer();
                };

                function nextSlide() {
                    let next = (currentSlide + 1) % totalSlides;
                    setBannerSlide(next);
                }

                function startTimer() {
                    slideInterval = setInterval(nextSlide, 4500); // Интервал 4.5 сек
                }

                function resetTimer() {
                    clearInterval(slideInterval);
                    startTimer();
                }

                // Запуск
                startTimer();

                // Пауза при наведении мыши (опционально, для удобства пользователя)
                const wrapper = document.getElementById('promoSlider');
                wrapper.addEventListener('mouseenter', () => clearInterval(slideInterval));
                wrapper.addEventListener('mouseleave', () => startTimer());
            });
        </script>
        @endif
    @endif
   
    {{-- БЛОК 1 — ЗАКАЗЫ --}}
    <h2 class="dash-block-title mt-5">Заказы и каталог</h2>
    <div class="dash-block-grid">
        @foreach($menu as $item)
            @if(isset($item['group']) && $item['group'] === 'orders' && in_array('dashboard', $item['show_in']))
                <a href="{{ $item['url'] }}" class="dash-card">
                    {{-- ИЗОБРАЖЕНИЕ В УГЛУ --}}
                    @if(!empty($item['image']))
                        <img src="{{ asset($item['image']) }}" class="dash-card-image" alt="">
                    @endif

                    <i class="bi {{ $item['icon'] }}"></i>
                    <div class="dash-card-title">{{ $item['title'] }}</div>
                    <div class="dash-card-desc">{{ $item['desc'] }}</div>
                </a>
            @endif
        @endforeach
    </div>

    {{-- БЛОК 2 — БИЗНЕС --}}
    <h2 class="dash-block-title mt-5">Бизнес и организации</h2>
    <div class="dash-block-grid">
        @foreach($menu as $item)
            @if(isset($item['group']) && $item['group'] === 'business' && in_array('dashboard', $item['show_in']))
                <a href="{{ $item['url'] }}" class="dash-card">
                    {{-- ИЗОБРАЖЕНИЕ В УГЛУ --}}
                    @if(!empty($item['image']))
                        <img src="{{ asset($item['image']) }}" class="dash-card-image" alt="">
                    @endif

                    <i class="bi {{ $item['icon'] }}"></i>
                    <div class="dash-card-title">{{ $item['title'] }}</div>
                    <div class="dash-card-desc">{{ $item['desc'] }}</div>
                </a>
            @endif
        @endforeach
    </div>

    {{-- БЛОК 3 — СЕРВИС --}}
    <h2 class="dash-block-title mt-5">Настройки и сервисы</h2>
    <div class="dash-block-grid">
        @foreach($menu as $item)
            @if(isset($item['group']) && $item['group'] === 'settings' && in_array('dashboard', $item['show_in']))
                @php $isLogout = str_contains($item['url'], 'logout'); @endphp

                <a href="{{ $isLogout ? '#' : $item['url'] }}" 
                   class="dash-card {{ $isLogout ? 'dash-card-red' : '' }}"
                   @if($isLogout) 
                     onclick="event.preventDefault(); openModal('universalConfirm', () => { document.getElementById('logout-form-dash').submit(); }, 'Выход из системы', 'Вы действительно хотите выйти?') "
                   @endif>
                    
                    {{-- ИЗОБРАЖЕНИЕ В УГЛУ --}}
                    @if(!empty($item['image']))
                        <img src="{{ asset($item['image']) }}" class="dash-card-image" alt="">
                    @endif

                    <i class="bi {{ $item['icon'] }}"></i>
                    <div class="dash-card-title">{{ $item['title'] }}</div>
                    <div class="dash-card-desc">{{ $item['desc'] }}</div>
                </a>

                @if($isLogout)
                    <form id="logout-form-dash" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                @endif
            @endif
        @endforeach
    </div>
@endsection