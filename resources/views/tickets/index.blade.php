@extends('layouts.app')

@section('title', 'Обращения')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/requests.css') }}">
@endpush

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> → <span>Обращения</span>
    </div>

    <h1 class="page-title">Обращения</h1>
    <p class="page-subtitle">Ваши заявки в службу поддержки</p>

    <a href="{{ route('tickets.create') }}" class="btn btn-primary mb-20" style="margin-bottom: 20px;">
        <i class="bi bi-plus-circle"></i> Создать обращение
    </a>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if($tickets->isEmpty())
        <div class="alert alert-info mt-20">
            <i class="bi bi-info-circle"></i>
            У вас пока нет обращений.
        </div>
    @else
        <div class="request-list">
            @foreach($tickets as $ticket)
                <a href="{{ route('tickets.show', $ticket->request_code) }}" class="request-card">
                    <div class="request-card-head">
                        <div class="request-title">{{ $ticket->topic }}</div>
                        <div class="request-status status-{{ $ticket->status }}">
                            {{ $ticket->status_label }}
                        </div>
                    </div>

                    <div class="request-meta">
                        <span>№ {{ $ticket->request_code }}</span>
                        <span>{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
                        @if($ticket->org_name)
                            <span>{{ $ticket->org_name }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="pagination" style="margin-top: 30px; display: flex; justify-content: center;">
            {{ $tickets->links() }}
        </div>
    @endif
@endsection