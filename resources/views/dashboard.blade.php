@extends('layouts.app')

@section('title', 'Дашборд — ' . config('b2b.app_name'))

@section('content')

    {{-- ПОДГОТОВКА ДАННЫХ --}}
    @php
        $user = Auth::user();
        $profile = $user->profile; // Связь с таблицей user_profiles

        $roleName = match($user->role) {
            'admin'   => 'Администратор',
            'manager' => 'Менеджер',
            default   => 'Партнёр', // Для role = 'partner' или пустого
        };

        // Используем Carbon для красивого формата "28.12.2025 в 14:30"
        $lastLogin = $user->last_login 
            ? \Carbon\Carbon::parse($user->last_login)->timezone('Europe/Moscow')->format('d.m.Y в H:i') 
            : 'Только что';
    @endphp

    {{-- ЗАГОЛОВОК --}}
    <h1 class="page-title">
        Здравствуйте, {{ $profile->first_name }} {{ $profile->middle_name }}!
    </h1>

    {{-- ИНФО-БЛОК --}}
    <div style="margin-bottom: 10px; margin-top: 10px; line-height: 1.4; color: #001F33;">
        {{-- Используем переменные, переданные из контроллера --}}
        <div>Последний раз вы заходили: <strong>{{ $lastLoginText }}</strong></div>
        <div>Уровень доступа к сервису: <strong>{{ $roleName }}</strong></div>
    </div>
   
    {{-- Вывод уведомления о статусе организации (если есть) --}}
    @if(isset($org_status) && !empty($org_status['text']))
        <div class="info-message">
            {!! $org_status['text'] !!} {{-- Используем {!! !!} вместо {{ }} --}}
        </div>
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
                    // Максимально широкая проверка на Logout, чтобы точно поймать нужный пункт
                    $isLogout = str_contains($item['url'], 'logout');
                @endphp

                <a href="{{ $isLogout ? route('logout') : $item['url'] }}" 
                   class="dash-card {{ $isLogout ? 'dash-card-red' : '' }}"
                   @if($isLogout) 
                     onclick="event.preventDefault(); openModal('universalConfirm', () => { document.getElementById('logout-form-dash').submit(); }, 'Выход из системы', 'Вы действительно хотите завершить текущую сессию и выйти из личного кабинета?')"
                   @endif>
                    
                    <i class="bi {{ $item['icon'] }}"></i>
                    <div class="dash-card-title">{{ $item['title'] }}</div>
                    <div class="dash-card-desc">{{ $item['desc'] }}</div>
                </a>

                {{-- Создаем форму только если это пункт Logout --}}
                @if($isLogout)
                    <form id="logout-form-dash" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endif

            @endif
        @endforeach
    </div>
@endsection