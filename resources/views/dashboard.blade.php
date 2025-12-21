@extends('layouts.app')

@section('title', 'Дашборд — ' . config('b2b.app_name'))

@section('content')
    <h1 class="page-title">
        Добро пожаловать{{ Auth::user()->company ? ', ' . Auth::user()->company : '' }}!
    </h1>

    <p class="page-subtitle">
        Роль: <strong>{{ Auth::user()->role ?: 'partner' }}</strong>
    </p>
   
    {{-- Вывод уведомления о статусе организации --}}
    @if(isset($org_status) && !empty($org_status['text']))
        <div class="info-message">{{ $org_status['text'] }}</div>
    @endif

    {{-- ============================
         БЛОК 1 — ЗАКАЗЫ И КАТАЛОГ
         ============================ --}}
    <h2 class="dash-block-title">Заказы и каталог</h2>
    <div class="dash-block-grid">
        @foreach($menu as $item)
            @if($item['group'] === 'orders' && in_array('dashboard', $item['show_in']))
                <a href="{{ $item['url'] }}" class="dash-card">
                    <i class="bi {{ $item['icon'] }}"></i>
                    <div class="dash-card-title">{{ $item['title'] }}</div>
                    <div class="dash-card-desc">{{ $item['desc'] }}</div>
                </a>
            @endif
        @endforeach
    </div>

    {{-- ============================
         БЛОК 2 — БИЗНЕС, ОРГАНИЗАЦИИ И ДОКУМЕНТЫ
         ============================ --}}
    <h2 class="dash-block-title mt-5">Бизнес, организации и документы</h2>
    <div class="dash-block-grid">
        @foreach($menu as $item)
            @if($item['group'] === 'business' && in_array('dashboard', $item['show_in']))
                <a href="{{ $item['url'] }}" class="dash-card">
                    <i class="bi {{ $item['icon'] }}"></i>
                    <div class="dash-card-title">{{ $item['title'] }}</div>
                    <div class="dash-card-desc">{{ $item['desc'] }}</div>
                </a>
            @endif
        @endforeach
    </div>

    {{-- ============================
         БЛОК 3 — НАСТРОЙКИ И СЕРВИСЫ
         ============================ --}}
    <h2 class="dash-block-title mt-5">Настройки и сервисы</h2>
    <div class="dash-block-grid">
        @foreach($menu as $item)
            @if($item['group'] === 'settings' && in_array('dashboard', $item['show_in']))
                @php
                    // Проверяем на Logout для красной карточки, как в оригинале
                    $isLogout = ($item['url'] === '/partners-area/logout');
                @endphp
                <a href="{{ $item['url'] }}" 
                   class="dash-card {{ $isLogout ? 'dash-card-red' : '' }}">
                    <i class="bi {{ $item['icon'] }}"></i>
                    <div class="dash-card-title">{{ $item['title'] }}</div>
                    <div class="dash-card-desc">{{ $item['desc'] }}</div>
                </a>
            @endif
        @endforeach
    </div>
@endsection