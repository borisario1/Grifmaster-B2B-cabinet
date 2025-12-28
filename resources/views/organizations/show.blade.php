@extends('layouts.app')

@section('title', 'Просмотр организации')

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('organizations.index') }}">Мои организации</a> →
        <span>{{ $organization->name }}</span>
    </div>

    {{-- Заголовок и подзаголовок как в оригинале --}}
    <h1 class="page-title">{{ $organization->name }}</h1>
    <p class="page-subtitle">
        {{ $organization->type === 'ip' ? 'Индивидуальный предприниматель' : 'Юридическое лицо' }}
    </p>

    {{-- БЛОК 1: Основные данные (из нашей базы) --}}
    <div class="profile-section">
        <h2 class="section-title">
            <i class="bi bi-building menu-icon"></i> Данные организации
        </h2>
        <div class="card-info">
            <div class="info-row">
                <span>ИНН:</span>
                <strong>{{ $organization->inn }}</strong>
            </div>
            <div class="info-row">
                <span>КПП:</span>
                <strong>{{ $organization->kpp ?: '—' }}</strong>
            </div>
            <div class="info-row">
                <span>ОГРН:</span>
                <strong>{{ $organization->ogrn ?: '—' }}</strong>
            </div>
            <div class="info-row">
                <span>Полное название:</span>
                <strong>{{ $organization->info->name_full ?? $organization->name }}</strong>
            </div>
            <div class="info-row">
                <span>Адрес:</span>
                <strong>{{ $organization->address ?: '—' }}</strong>
            </div>
        </div>
    </div>

    {{-- БЛОК 2: Данные из ФНС (DaData) --}}
    {{-- Логика перенесена из твоего view.php --}}
    @if($organization->info && !empty($organization->info->dadata_raw))
        @php
            // Получаем массив данных (Laravel автоматически декодирует JSON, если настроен cast, 
            // но для надежности берем как массив, если это array, или декодируем)
            $dadata = is_array($organization->info->dadata_raw) 
                ? $organization->info->dadata_raw 
                : json_decode($organization->info->dadata_raw, true);

            // Статус
            $st = $dadata['state']['status'] ?? null;
            $statusRaw = strtoupper(trim((string)$st));
            $statusHuman = match($statusRaw) {
                'ACTIVE'       => 'Действующая',
                'LIQUIDATED'   => 'Ликвидирована',
                'LIQUIDATING'  => 'В процессе ликвидации',
                'BANKRUPT'     => 'Банкротство',
                'REORGANIZING' => 'Реорганизация',
                default        => $statusRaw,
            };

            // Дата регистрации
            $reg_ts = $dadata['state']['registration_date'] ?? null;
            $reg_date = $reg_ts ? date('d.m.Y', $reg_ts / 1000) : '—';

            // Адрес
            $addr = $dadata['address']['unrestricted_value'] 
                 ?? $dadata['address']['value'] 
                 ?? '—';

            // ОПФ
            $opf = $dadata['opf']['full'] 
                ?? $dadata['opf']['short'] 
                ?? null;

            // Руководитель
            $mgmt = trim(
                ($dadata['management']['name'] ?? '') . ' ' . 
                ($dadata['management']['post'] ?? '')
            );

            // ОКВЭДы
            $okved = $dadata['okved'] ?? null;
            $okveds = $dadata['okveds'] ?? [];

            // Коды
            $codes = [
                'ОКПО'  => $dadata['okpo'] ?? null,
                'ОКАТО' => $dadata['okato'] ?? null,
                'ОКТМО' => $dadata['oktmo'] ?? null,
                'ОКОГУ' => $dadata['okogu'] ?? null,
                'ОКФС'  => $dadata['okfs'] ?? null,
            ];

            // Финансы
            $finance = $dadata['finance'] ?? null;
        @endphp

        <div class="profile-section mt-4">
            <h2 class="section-title">
                <i class="bi bi-card-list menu-icon"></i> Данные из ФНС
            </h2>

            <div class="card-info">
                <div class="info-row">
                    <span>Статус:</span>
                    <strong>{{ $statusHuman }}</strong>
                </div>

                @if($opf)
                    <div class="info-row">
                        <span>ОПФ:</span>
                        <strong>{{ $opf }}</strong>
                    </div>
                @endif

                @if($mgmt)
                    <div class="info-row">
                        <span>Руководитель:</span>
                        <strong>{{ $mgmt }}</strong>
                    </div>
                @endif

                <div class="info-row">
                    <span>Дата регистрации:</span>
                    <strong>{{ $reg_date }}</strong>
                </div>

                <div class="info-row">
                    <span>Адрес регистрации:</span>
                    <strong>{{ $addr }}</strong>
                </div>

                {{-- Вывод кодов (ОКПО, ОКАТО...) --}}
                @foreach($codes as $label => $v)
                    @if($v)
                        <div class="info-row">
                            <span>{{ $label }}:</span>
                            <strong>{{ $v }}</strong>
                        </div>
                    @endif
                @endforeach

                {{-- Основной ОКВЭД --}}
                @if($okved)
                    <div class="info-row">
                        <span>Основной ОКВЭД:</span>
                        <strong>{{ $okved }}</strong>
                    </div>
                @endif

                {{-- Доп ОКВЭДы --}}
                @if(!empty($okveds))
                    <div class="info-row">
                        <span>Доп. ОКВЭДы:</span>
                        <div>
                            @foreach($okveds as $o)
                                • {{ $o['code'] ?? '' }}<br>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Финансы --}}
                @if($finance)
                    @foreach($finance as $label => $value)
                        @if($value !== null && $value !== '' && $value !== '-1')
                            <div class="info-row">
                                <span>{{ $label }}:</span>
                                <strong>{{ $value }}</strong>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    @else
        {{-- Если данных нет, но инфо запись существует (пустая) --}}
        <div class="profile-section mt-4">
             <h2 class="section-title">
                <i class="bi bi-card-list menu-icon"></i> Данные из ФНС
            </h2>
            <p class="empty-block">Расширенные данные отсутствуют.</p>
        </div>
    @endif
    
    {{-- БЛОК 3: Добавлено пользователем --}}
    <div class="profile-section mt-4">
        <h2 class="section-title">
            <i class="bi bi-person-badge menu-icon"></i> Добавлено пользователем
        </h2>
        
        <div class="card-info">
            <div class="info-row">
                <span>Email:</span>
                <strong>{{ $organization->user->email }}</strong>
            </div>

            <div class="info-row">
                <span>Телефон:</span>
                <strong>{{ $organization->user->phone ?: '—' }}</strong>
            </div>

            <div class="info-row">
                <span>Роль:</span>
                <strong>{{ $organization->user->role ?: '—' }}</strong>
            </div>

            <div class="info-row">
                <span>Создан:</span>
                <strong>{{ $organization->created_at->format('d.m.Y H:i') }}</strong>
            </div>

            <div class="info-row">
                <span>Последний вход:</span>
                <strong>{{ $organization->user->last_login ?? '—' }}</strong>
            </div>
        </div>
    </div>

    <br>
    <a href="{{ route('organizations.index') }}" class="btn-link-back">← Назад</a>
    
@endsection