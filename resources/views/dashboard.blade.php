@extends('layouts.app')

@section('title', 'Дашборд — Партнёрская территория')

@section('content')
    <h1 class="page-title">
        Добро пожаловать{{ Auth::user()->company ? ', ' . Auth::user()->company : '' }}!
    </h1>

    <p class="page-subtitle">
        Роль: <strong>{{ Auth::user()->role ?: 'partner' }}</strong>
    </p>

    {{-- Вывод уведомления о статусе организации (если есть такая логика) --}}
    @if(isset($org_status) && !empty($org_status['text']))
        <div class="info-message">{{ $org_status['text'] }}</div>
    @endif

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

    <h2 class="dash-block-title mt-5">Бизнес-инструменты</h2>
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

    <h2 class="dash-block-title mt-5">Настройки и сервисы</h2>
    <div class="dash-block-grid">
        @foreach($menu as $item)
            @if($item['group'] === 'settings' && in_array('dashboard', $item['show_in']))
                <a href="{{ $item['url'] }}" 
                   class="dash-card {{ Str::contains($item['url'], 'logout') ? 'dash-card-red' : '' }}">
                    <i class="bi {{ $item['icon'] }}"></i>
                    <div class="dash-card-title">{{ $item['title'] }}</div>
                    <div class="dash-card-desc">{{ $item['desc'] }}</div>
                </a>
            @endif
        @endforeach
    </div>
@endsection