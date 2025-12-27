@extends('layouts.app')

@section('title', 'Мои организации')

@section('content')

    {{-- Хлебные крошки --}}
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> →
        <span>Мои организации</span>
    </div>

    <h1 class="page-title">Мои организации</h1>

    <p>
        <a href="{{ route('organizations.create') }}" class="btn-primary">
            <i class="bi bi-plus-circle"></i>
            Добавить организацию
        </a>
    </p>

    {{-- Сообщения об успехе --}}
    @if(session('ok'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <i class="bi bi-check-circle-fill"></i> {{ session('ok') }}
        </div>
    @endif

    {{-- Если список пуст --}}
    @if($organizations->isEmpty())

        <div class="empty-block">
            У вас пока нет сохранённых организаций.
            <br><br>
            <a href="{{ route('dashboard') }}" class="btn-link-back">← Вернуться в кабинет</a>
        </div>

    @else

        <div class="org-list">

            @foreach ($organizations as $org)
                @php
                    // Получаем ID выбранной организации из профиля юзера
                    // Приводим к int, чтобы null превратился в 0
                    $userSelectedId = (int) auth()->user()->selected_org_id;
                    
                    // ID текущей организации в цикле
                    $currentOrgId = (int) $org->id;
                    
                    // Сравниваем числа
                    $isSelected = ($userSelectedId > 0 && $userSelectedId === $currentOrgId);
                    
                    $selectUrl = route('organizations.select', $org->id);
                @endphp

                {{-- КАРТОЧКА --}}
                {{-- Если выбрана, добавляем класс 'selected' для зеленой обводки --}}
                <div class="org-card {{ $isSelected ? 'selected' : '' }}" onclick="selectOrg('{{ $selectUrl }}')">

                    {{-- ЛЕВАЯ ЧАСТЬ (Инфо) --}}
                    <div>
                        <div class="org-card-title">
                            @if($org->type === 'ip')
                                <i class="bi bi-person-workspace"></i>
                                {{-- Имя ИП --}}
                            @else
                                <i class="bi bi-building-check"></i>
                            @endif

                            {{ $org->name }}

                            {{-- Значок DaData --}}
                            @if($org->info)
                                <i class="bi bi-patch-check-fill org-dadata-check" 
                                   title="Данные по организации получены из ФНС"
                                   style="color: #198754;"></i>
                            @endif
                        </div>

                        <div class="org-card-details">
                            ИНН: {{ $org->inn }}<br>

                            @if($org->type === 'ip')
                                ОГРНИП: {{ $org->ogrn ?: '—' }}
                            @else
                                КПП: {{ $org->kpp ?: '—' }}<br>
                                ОГРН: {{ $org->ogrn ?: '—' }}
                            @endif
                        </div>
                    </div>

                    {{-- ПРАВАЯ ЧАСТЬ (Кнопки) --}}
                    <div class="org-card-right">

                        @if($isSelected)
                            {{-- ВАРИАНТ 1: Уже выбрана (просто лейбл) --}}
                            <div class="org-selected-label">
                                <i class="bi bi-check-circle-fill"></i> Выбрана
                            </div>
                        @else
                            {{-- ВАРИАНТ 2: Не выбрана (кнопка "Выбрать") --}}
                            {{-- stopPropagation: чтобы клик не дублировал событие родителя --}}
                            <a class="org-action select" href="{{ $selectUrl }}" onclick="event.stopPropagation()">
                                <i class="bi bi-check-circle"></i> Выбрать
                            </a>
                        @endif

                        {{-- Кнопка Посмотреть --}}
                        <a class="org-action" href="{{ route('organizations.show', $org->id) }}" onclick="event.stopPropagation()">
                            <i class="bi bi-eye"></i> Посмотреть
                        </a>

                        {{-- Кнопка Удалить --}}
                        <a class="org-action delete" href="#" onclick="deleteOrg(event, '{{ $org->id }}')">
                            <i class="bi bi-trash"></i> Удалить
                        </a>
                        
                        {{-- Скрытая форма для удаления --}}
                        <form id="delete-form-{{ $org->id }}" action="{{ route('organizations.destroy', $org->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>

                    </div>

                </div>
            @endforeach

        </div>

        <br>
        <a href="{{ route('dashboard') }}" class="btn-link-back">← Вернуться в кабинет</a>

    @endif

    <div class="auth-tech-footer mt-4 text-center" style="opacity: 0.6; font-size: 0.85rem;">
         © {{ date('Y') }} {{ config('b2b.app_name') }}
    </div>

{{-- JS Скрипты --}}
<script>
function selectOrg(url) {
    window.location.href = url;
}

function deleteOrg(event, id) {
    event.stopPropagation();
    event.preventDefault();
    
    if(confirm('Удалить эту организацию?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endsection