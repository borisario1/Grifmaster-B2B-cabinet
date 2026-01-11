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
    @if(isset($org_status) && !empty($org_status['text']))
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
    <div class="card-info" style="padding: 15px 20px; border-radius: var(--min_radius);">
        <div style="font-size: 15px; color: #666; display: flex; flex-wrap: wrap; gap: 20px;">
            <div>Последний вход: <strong style="color: #001F33; font-weight: 500;">{{ $lastLoginText }}</strong></div>
            <div>Ваш статус: <strong style="color: #001F33; font-weight: 500;">{{ $roleName }}</strong></div>
        </div>
    </div>
   
    {{-- БЛОК 1 — ЗАКАЗЫ --}}
    <h2 class="dash-block-title mt-5">Заказы и каталог</h2>
    <div class="dash-block-grid">
        @foreach($menu as $item)
            @if(isset($item['group']) && $item['group'] === 'orders' && in_array('dashboard', $item['show_in']))
                <a href="{{ $item['url'] }}" class="dash-card">
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