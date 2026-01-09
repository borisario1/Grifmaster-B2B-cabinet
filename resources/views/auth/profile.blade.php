@extends('layouts.app')

@section('title', 'Мой профиль — ' . config('b2b.app_name'))

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> →
        <span>Мой профиль</span>
    </div>

    <h1 class="page-title">Мой профиль</h1>
    <p class="page-subtitle">Ваши данные в системе</p>

    {{-- Вывод ошибок (оставляем блоком, так как их может быть много) --}}
    @if($errors->any())
        <div class="form-error">
            @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <div class="profile-section">
        <div class="section-title"><i class="bi bi-person-badge"></i> Личные данные</div>
        <div class="profile-view-grid">
            
            {{-- БЛОК: Авторизация --}}
            <div class="profile-view-card">
                <div class="group-title"><i class="bi bi-shield-lock"></i> Авторизация</div>
                <div class="profile-view-row"><span>Email (логин)</span> <strong>{{ $user->email }}</strong></div>
                <div class="profile-view-row"><span>Уровень доступа</span> <strong class="text-primary">{{ $roleName }}</strong></div>
                <div class="profile-view-row"><span>Дата регистрации</span> <strong>{{ $registrationDate }}</strong></div>
                <div class="profile-view-row"><span>Последний вход</span> <strong>{{ $lastLogin }}</strong></div>
                @if($user->phone)
                    <div class="profile-view-row"><span>Телефон при регистрации</span> <strong>{{ $user->phone }}</strong></div>
                @endif
            </div>

            {{-- БЛОК: Основные данные --}}
            <div class="profile-view-card">
                <div class="group-title"><i class="bi bi-person"></i> Основные данные</div>
                <div class="profile-view-row">
                    <span>ФИО</span> 
                    <strong>
                        {{ $user->profile->last_name ?? '—' }} 
                        {{ $user->profile->first_name ?? '' }} 
                        {{ $user->profile->middle_name ?? '' }}
                    </strong>
                </div>
                <div class="profile-view-row"><span>Дата рождения</span> <strong>{{ $birthDate }}</strong></div>
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
            <form method="POST" action="{{ route('profile.update') }}" class="form-block">
                @csrf
                <div class="form-two">
                    <div class="form-group">
                        <label>Фамилия *</label>
                        <input type="text" class="form-input" name="last_name" value="{{ old('last_name', $user->profile->last_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Имя *</label>
                        <input type="text" class="form-input" name="first_name" value="{{ old('first_name', $user->profile->first_name) }}" required>
                    </div>
                </div>
                <div class="form-two">
                    <div class="form-group">
                        <label>Отчество</label>
                        <input type="text" class="form-input" name="middle_name" value="{{ old('middle_name', $user->profile->middle_name) }}">
                    </div>
                    <div class="form-group">
                        <label>Дата рождения *</label>
                        <input type="date" class="form-input" name="birth_date" value="{{ old('birth_date', $user->profile->birth_date) }}" required>
                    </div>
                </div>
                <div class="form-two">
                    <div class="form-group">
                        <label>Рабочий телефон *</label>
                        <input type="text" class="form-input" name="work_phone" value="{{ old('work_phone', $user->profile->work_phone) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Мессенджер</label>
                        <input type="text" class="form-input" name="messenger" value="{{ old('messenger', $user->profile->messenger) }}">
                    </div>
                </div>
                <button class="btn-primary btn-big" type="submit"><i class="bi bi-save"></i> Сохранить данные</button>
            </form>
        </div>

        {{-- ВКЛАДКА 2 — УВЕДОМЛЕНИЯ (БЕЗ ИЗМЕНЕНИЙ СТИЛЕЙ) --}}
        <div class="tab-section" id="tab-notify" style="display:none;">
            <div class="section-title"><i class="bi bi-bell"></i> Настройки Email уведомлений</div>
            <div class="toggle-row">
                <div class="toggle-text"><strong>Общие уведомления</strong><div class="toggle-desc">Системные сообщения о работе личного кабинета.</div></div>
                <label class="switch"><input class="notify-toggle" type="checkbox" name="notify_general" {{ $user->profile->notify_general ? 'checked' : '' }}><span class="slider"></span></label>
            </div>
            <div class="toggle-row">
                <div class="toggle-text"><strong>Скидки, акции и новости</strong><div class="toggle-desc">Сообщения о скидках и изменениях в ассортименте.</div></div>
                <label class="switch"><input class="notify-toggle" type="checkbox" name="notify_news" {{ $user->profile->notify_news ? 'checked' : '' }}><span class="slider"></span></label>
            </div>
            <div class="toggle-row">
                <div class="toggle-text"><strong>Заказы и документы</strong><div class="toggle-desc">Уведомления по заказам, отгрузкам и документам.</div></div>
                <label class="switch"><input class="notify-toggle" type="checkbox" name="notify_orders" {{ $user->profile->notify_orders ? 'checked' : '' }}><span class="slider"></span></label>
            </div>
            <div class="toggle-row">
                <div class="toggle-text"><strong>Обращения и заявки</strong><div class="toggle-desc">Ответы по вашим заявкам и рекламациям.</div></div>
                <label class="switch"><input class="notify-toggle" type="checkbox" name="notify_ticket" {{ $user->profile->notify_ticket ? 'checked' : '' }}><span class="slider"></span></label>
            </div>
            <div class="toggle-row">
                <div class="toggle-text"><strong>Сообщения менеджера</strong><div class="toggle-desc">Важные сообщения. Должно быть включено.</div></div>
                <label class="switch"><input type="checkbox" checked disabled><span class="slider"></span></label>
            </div>
        </div>

        {{-- ВКЛАДКА 3 — ПАРОЛЬ --}}
        <div class="tab-section" id="tab-password" style="display:none;">
            <div class="section-title"><i class="bi bi-key"></i> Смена пароля</div>
            <form method="POST" action="{{ route('profile.password') }}" class="form-block">
                @csrf
                <div class="form-group">
                    <label>Текущий пароль</label>
                    <input type="password" class="form-input" name="old_password" required>
                </div>
                <div class="form-two">
                    <div class="form-group">
                        <label>Новый пароль</label>
                        <input type="password" class="form-input" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Повторите пароль</label>
                        <input type="password" class="form-input" name="new_password_confirmation" required>
                    </div>
                </div>
                <button class="btn-primary btn-big" type="submit">Изменить пароль</button>
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

        document.querySelectorAll(".notify-toggle").forEach(sw => {
            sw.addEventListener("change", function () {
                const data = { name: this.name, value: this.checked ? 1 : 0 };
                fetch("{{ route('profile.notify') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (response.ok) {
                        showToast('Настройки уведомлений обновлены', 'bi-bell');
                    } else {
                        showToast('Ошибка сохранения', 'bi-exclamation-triangle', true);
                        this.checked = !this.checked;
                    }
                })
                .catch(() => {
                    showToast('Ошибка сети', 'bi-wifi-off', true);
                    this.checked = !this.checked;
                });
            });
        });
    });
    </script>
@endsection