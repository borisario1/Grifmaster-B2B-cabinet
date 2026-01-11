@extends('layouts.app')

@section('title', 'Мои организации')

@section('content')

    {{-- Хлебные крошки --}}
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> →
        <span>Мои организации</span>
    </div>

    <h1 class="page-title">Мои организации</h1>

    <p style="margin-bottom: 25px;">
        {{-- Кнопка добавления: теперь большая и системная --}}
        <a href="{{ route('organizations.create') }}" class="btn-primary btn-big">
            <i class="bi bi-plus-circle"></i>
            Добавить организацию
        </a>
    </p>

    @if($organizations->isEmpty())

        <div class="empty-block">
            У вас пока нет сохранённых организаций.
            <br>
            <a href="{{ route('dashboard') }}" class="btn-link-back">← Вернуться в кабинет</a>
        </div>

    @else

        <div class="org-list">

            @foreach ($organizations as $org)
                @php
                    $userSelectedId = (int) auth()->user()->selected_org_id;
                    $currentOrgId = (int) $org->id;
                    $isSelected = ($userSelectedId > 0 && $userSelectedId === $currentOrgId);
                    $selectUrl = route('organizations.select', $org->id);
                @endphp

                <div class="org-card {{ $isSelected ? 'selected' : '' }}" onclick="selectOrg('{{ $selectUrl }}')">

                    {{-- ИНФОРМАЦИЯ --}}
                    <div>
                        <div class="org-card-title">
                            @if($org->type === 'ip')
                                <i class="bi bi-person-workspace"></i>
                            @else
                                <i class="bi bi-building-check"></i>
                            @endif

                            {{ $org->name }}

                            @if($org->info)
                                <i class="bi bi-patch-check-fill org-dadata-check" 
                                   title="Данные подтверждены DaData"
                                   style="color: #198754;"></i>
                            @endif
                        </div>

                        <div class="org-card-details">
                            ИНН: {{ $org->inn }}<br>
                            @if($org->type === 'ip')
                                ОГРНИП: {{ $org->ogrn ?: '—' }}
                            @else
                                КПП: {{ $org->kpp ?: '—' }} | ОГРН: {{ $org->ogrn ?: '—' }}
                            @endif
                        </div>
                    </div>

                    {{-- ДЕЙСТВИЯ --}}
                    <div class="org-card-right">

                        @if($isSelected)
                            <div class="org-selected-label">
                                <i class="bi bi-check-circle-fill"></i> Выбрана
                            </div>
                        @else
                            <a class="org-action select" href="{{ $selectUrl }}" onclick="event.stopPropagation()">
                                <i class="bi bi-check-circle"></i> Выбрать
                            </a>
                        @endif

                        <a class="org-action" href="{{ route('organizations.show', $org->id) }}" onclick="event.stopPropagation()">
                            <i class="bi bi-eye"></i> Просмотреть
                        </a>

                        <a class="org-action delete" href="#" 
                           onclick="event.stopPropagation(); event.preventDefault(); 
                           openModal(
                               'universalConfirm', 
                               () => { document.getElementById('delete-form-{{ $org->id }}').submit(); }, 
                               'Удаление организации', 
                               'Вы уверены, что хотите удалить «{{ $org->name }}»? Все связанные данные и корзина этой организации станут недоступны, а заказы будут перенесены в архив и получат отметку «Организация удалена». Это действие нельзя отменить!', 
                               30, 
                               'Точно удалить'
                           )">
                            <i class="bi bi-trash"></i> Удалить
                        </a>
                        
                        <form id="delete-form-{{ $org->id }}" action="{{ route('organizations.destroy', $org->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>

                </div>
            @endforeach

        </div>

        <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
            <a href="{{ route('dashboard') }}" class="btn-link-back">← Вернуться в кабинет</a>
        </div>
    @endif
    
<script>
function selectOrg(url) {
    window.location.href = url;
}
</script>
@endsection