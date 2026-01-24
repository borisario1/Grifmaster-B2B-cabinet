@extends('layouts.app')

@section('title', 'Новое обращение')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/requests.css') }}">
@endpush

@section('content')
    <div class="breadcrumbs">
        <a href="{{ route('dashboard') }}">Главная</a> →
        <a href="{{ route('tickets.index') }}">Обращения</a> →
        <span>Новое обращение</span>
    </div>

    <h1 class="page-title">Новое обращение</h1>
    <p class="page-subtitle">Опишите вашу ситуацию — мы ответим.</p>

    @if($errors->any())
        <div class="alert alert-danger" style="color: red; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tickets.store') }}" class="form-grid" style="max-width: 600px;">
        @csrf

        <!-- Категории -->
        <div class="form-group">
            <label>Категория *</label>
            <select name="category" class="form-input" required>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Тема -->
        <div class="form-group">
            <label>Тема *</label>
            <input type="text" name="topic" class="form-input" required placeholder="Краткая суть вопроса" value="{{ old('topic') }}">
        </div>

        <!-- Сообщение -->
        <div class="form-group">
            <label>Сообщение *</label>
            <textarea name="message" rows="5" class="form-input" required placeholder="Подробное описание">{{ old('message') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-lg mt-20">
            <i class="bi bi-send"></i> Отправить обращение
        </button>
    </form>
@endsection