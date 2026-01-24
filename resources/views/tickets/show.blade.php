@extends('layouts.app')

@section('title', $ticket->topic)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/requests.css') }}">
@endpush

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> →
        <a href="{{ route('tickets.index') }}">Обращения</a> →
        <span>{{ $ticket->topic }}</span>
    </div>

    <h1 class="page-title">{{ $ticket->topic }}</h1>
    <p class="page-subtitle">
        № {{ $ticket->request_code }} ·
        Статус: <strong>{{ $ticket->status === 'open' ? 'Открыто' : ($ticket->status === 'closed' ? 'Закрыто' : $ticket->status) }}</strong>
    </p>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <!-- INFO карточка -->
    <div class="card-info">
        <div class="info-row">
            <span>Номер обращения</span>
            <strong>{{ $ticket->request_code }}</strong>
        </div>
        <div class="info-row">
            <span>Категория</span>
            <strong>{{ $ticket->category_label }}</strong>
        </div>
        <div class="info-row">
            <span>Создано</span>
            <strong>{{ $ticket->created_at->format('d.m.Y H:i') }}</strong>
        </div>
        @if($ticket->org_name)
        <div class="info-row">
            <span>Организация</span>
            <strong>{{ $ticket->org_name }}</strong>
        </div>
        @endif
    </div>

    <!-- Чат -->
    <div class="chat-box" id="chatBox">
        @foreach($ticket->messages as $msg)
            @if($msg->sender_type === 'system')
                <div class="msg msg-system">
                    {!! nl2br(e($msg->message)) !!}
                    <div class="msg-time">{{ $msg->created_at->format('d.m.Y H:i') }}</div>
                </div>
            @else
                <div class="msg {{ $msg->sender_type === 'user' ? 'msg-user' : 'msg-staff' }}">
                    <div class="msg-text">{!! nl2br(e($msg->message)) !!}</div>
                    <div class="msg-time">{{ $msg->created_at->format('d.m.Y H:i') }}</div>
                </div>
            @endif
        @endforeach
    </div>

    @if($ticket->status !== 'closed')
        <form method="POST" action="{{ route('tickets.message', $ticket->request_code) }}" class="chat-send">
            @csrf
            <div class="form-group">
                <textarea name="message" class="form-input" rows="3" placeholder="Введите сообщение..." required></textarea>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send-fill"></i> Отправить
                </button>

                <a href="{{ route('tickets.close', $ticket->request_code) }}" 
                   class="btn btn-secondary" 
                   onclick="return confirm('Вы уверены, что хотите закрыть обращение?');">
                    Закрыть обращение
                </a>
            </div>
        </form>
    @else
        <div class="alert alert-info mt-20">
            <i class="bi bi-check-circle"></i>
            Это обращение закрыто. Если у вас возник новый вопрос, пожалуйста, создайте новое обращение.
        </div>
    @endif

    <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
        <a href="{{ route('tickets.index') }}" class="btn-link-back">← К списку обращений</a>
    </div>

    <script>
        const box = document.getElementById('chatBox');
        if (box) box.scrollTop = box.scrollHeight;
    </script>
@endsection
