@extends('layouts.app')

@section('title', 'Уведомления')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
@endpush

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> → <span>Уведомления</span>
    </div>

    <h1 class="page-title">Уведомления</h1>
    <p class="page-subtitle">Важные события и сообщения системы</p>

        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 20px; color: green;">
                {{ session('success') }}
            </div>
        @endif

        @if(isset($unreadCount) && $unreadCount > 0)
        <div class="notif-read-all-wrap">
            <button type="button" id="btn-read-all" class="btn btn-secondary">
                Пометить все как прочитанные
            </button>
        </div>
        @endif

        <div class="notif-list">
            @forelse($notifications as $notification)
                <div class="notif-item {{ !$notification->is_read ? 'unread' : '' }}" id="notif-item-{{ $notification->id }}">
                    <div class="notif-title">{{ $notification->title }}</div>
                    <div class="notif-message">
                        {!! nl2br(e($notification->message)) !!}
                    </div>
                    <div class="notif-meta">
                        <span>{{ $notification->created_at->format('d.m.Y H:i') }}</span>
                        
                        <div class="notif-actions">
                            @if($notification->link_url)
                                <a href="{{ $notification->link_url }}" class="notif-link">Перейти и посмотреть</a>
                            @endif
                            
                            @if(!$notification->is_read)
                                <button type="button" 
                                        class="notif-btn-read js-mark-read" 
                                        data-id="{{ $notification->id }}"
                                        data-url="{{ route('notifications.read', $notification->id) }}">
                                    Прочитать
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-block" style="text-align: center; padding: 40px; color: #777;">
                    У вас пока нет уведомлений.
                </div>
            @endforelse
        </div>

        <div class="pagination" style="margin-top: 30px; display: flex; justify-content: center;">
            {{ $notifications->links() }}
        </div>

    <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
        <a href="{{ route('dashboard') }}" class="btn-link-back">← Вернуться в личный кабинет</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

            if (!csrfToken) {
                console.error('CSRF token not found in meta tags. Make sure <meta name="csrf-token" ...> is in your layout.');
            }

            // 1. Обработка кнопки "Прочитать" (одиночная)
            document.querySelectorAll('.js-mark-read').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!csrfToken) return;

                    const url = this.dataset.url;
                    const notifId = this.dataset.id;
                    const item = document.getElementById('notif-item-' + notifId);
                    const originalText = this.textContent;

                    // Визуально меняем сразу
                    this.textContent = '...';
                    this.disabled = true;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.textContent = 'Прочитано';
                            // Убираем класс unread
                            if (item) item.classList.remove('unread');
                            // Плавно скрываем кнопку
                            setTimeout(() => {
                                this.style.display = 'none';
                            }, 1000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.textContent = originalText;
                        this.disabled = false;
                        alert('Не удалось отметить как прочитанное. Попробуйте обновить страницу.');
                    });
                });
            });

            // 2. Обработка "Пометить все"
            const btnAll = document.getElementById('btn-read-all');
            if (btnAll) {
                btnAll.addEventListener('click', function() {
                    if (!csrfToken) return;
                    
                    const originalText = btnAll.textContent;
                    btnAll.textContent = 'Обработка...';
                    btnAll.disabled = true;

                    fetch('{{ route("notifications.read.all") }}', {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': csrfToken, 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            btnAll.textContent = 'Вы все прочитали';
                            document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
                            document.querySelectorAll('.js-mark-read').forEach(el => el.style.display = 'none');
                            setTimeout(() => { btnAll.style.display = 'none'; }, 1500);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        btnAll.textContent = originalText;
                        btnAll.disabled = false;
                        alert('Ошибка при обновлении статусов.');
                    });
                });
            }
        });
    </script>
@endsection