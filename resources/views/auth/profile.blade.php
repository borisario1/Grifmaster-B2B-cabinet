@extends('layouts.app')

@section('title', 'Мой профиль — ' . config('b2b.app_name'))

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> →
        <span>Мой профиль</span>
    </div>

    <h1 class="page-title">Мой профиль</h1>
    <p class="page-subtitle">Ваши данные в системе</p>

    {{-- Сообщения об успехе или ошибках --}}
    @if(session('ok'))
        <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> Данные сохранены</div>
    @endif
    @if(session('pwd_ok'))
        <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> Пароль изменен</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <div class="profile-section">
        <div class="section-title"><i class="bi bi-person-badge"></i> Личные данные</div>
        <div class="profile-view-grid">
            <div class="profile-view-card">
                <div class="group-title"><i class="bi bi-shield-lock"></i> Авторизация</div>
                <div class="profile-view-row"><span>Email (логин)</span> <strong>{{ $user->email }}</strong></div>
                <div class="profile-view-row"><span>Телефон при регистрации</span> <strong>{{ $user->phone }}</strong></div>
                <div class="profile-view-row"><span>Дата регистрации</span> <strong>{{ $user->created_at }}</strong></div>
            </div>

            <div class="profile-view-card">
                <div class="group-title"><i class="bi bi-person"></i> Основные данные</div>
                <div class="profile-view-row"><span>ФИО</span> 
                    <strong>{{ $user->profile->last_name ?? '—' }} {{ $user->profile->first_name ?? '' }} {{ $user->profile->middle_name ?? '' }}</strong>
                </div>
                <div class="profile-view-row"><span>Дата рождения</span> <strong>{{ $user->profile->birth_date ?? '—' }}</strong></div>
                <div class="profile-view-row"><span>Рабочий номер</span> <strong>{{ $user->profile->work_phone ?? '—' }}</strong></div>
                <div class="profile-view-row"><span>Мессенджер</span> <strong>{{ $user->profile->messenger ?? '—' }}</strong></div>
            </div>
        </div>
    </div>

    <div class="tabs-wrapper">
        <div class="tabs">
            <button class="tab-btn active" data-tab="tab-main">Контактная информация</button>
            <button class="tab-btn" data-tab="tab-notify">Настройка уведомлений</button>
            <button class="tab-btn" data-tab="tab-password">Сменить пароль</button>
        </div>

        {{-- ВКЛАДКА 1 — РЕДАКТИРОВАНИЕ --}}
        <div class="tab-section" id="tab-main" style="display:block;">
            <div class="section-title"><i class="bi bi-pencil-square"></i> Редактировать данные</div>
            <form method="POST" action="{{ route('profile.update') }}" class="form-grid">
                @csrf
                <div class="form-group">
                    <label>Фамилия *</label>
                    <input type="text" class="form-input" name="last_name" value="{{ old('last_name', $user->profile->last_name) }}" required>
                </div>
                <div class="form-group">
                    <label>Имя *</label>
                    <input type="text" class="form-input" name="first_name" value="{{ old('first_name', $user->profile->first_name) }}" required>
                </div>
                <div class="form-group">
                    <label>Отчество</label>
                    <input type="text" class="form-input" name="middle_name" value="{{ old('middle_name', $user->profile->middle_name) }}">
                </div>
                <div class="form-group">
                    <label>Дата рождения *</label>
                    <input type="date" class="form-input" name="birth_date" value="{{ old('birth_date', $user->profile->birth_date) }}" required>
                </div>
                <div class="form-group">
                    <label>Рабочий телефон *</label>
                    <input type="text" class="form-input" name="work_phone" value="{{ old('work_phone', $user->profile->work_phone) }}" required>
                </div>
                <div class="form-group">
                    <label>Мессенджер</label>
                    <input type="text" class="form-input" name="messenger" value="{{ old('messenger', $user->profile->messenger) }}">
                </div>
                <button class="btn btn-primary btn-lg mt-3" type="submit"><i class="bi bi-save"></i> Сохранить</button>
            </form>
        </div>

        {{-- ВКЛАДКА 2 — УВЕДОМЛЕНИЯ --}}
        <div class="tab-section" id="tab-notify" style="display:none;">
            <div class="section-title"><i class="bi bi-bell"></i> Настройки уведомлений</div>
            <p class="text-muted">Данный раздел находится в разработке и будет доступен в ближайшем обновлении.</p>
        </div>

        {{-- ВКЛАДКА 3 — ПАРОЛЬ --}}
        <div class="tab-section" id="tab-password" style="display:none;">
            <div class="section-title"><i class="bi bi-key"></i> Смена пароля</div>
            <form method="POST" action="{{ route('profile.password') }}" class="form-grid">
                @csrf
                <div class="form-group">
                    <label>Текущий пароль</label>
                    <input type="password" class="form-input" name="old_password" required>
                </div>
                <div class="form-group">
                    <label>Новый пароль</label>
                    <input type="password" class="form-input" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Повторите пароль</label>
                    <input type="password" class="form-input" name="new_password_confirmation" required>
                </div>
                <button class="btn btn-primary btn-lg mt-3" type="submit">Изменить пароль</button>
            </form>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll(".tab-btn");
    const sections = document.querySelectorAll(".tab-section");

    tabs.forEach(btn => {
        btn.addEventListener("click", () => {
            const target = btn.dataset.tab;
            tabs.forEach(t => t.classList.remove("active"));
            btn.classList.add("active");
            sections.forEach(sec => sec.style.display = sec.id === target ? "block" : "none");
        });
    });
});
</script>
@endsection