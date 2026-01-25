@extends('errors.layout')

@section('title', 'Слишком много запросов')
@section('code', '429')

@section('message')
    {{-- Выводим сообщение, пришедшее с сервера (из Middleware) --}}
    {!! nl2br(e($exception->getMessage())) ?: 'Слишком много запросов.<br>Пожалуйста, подождите.' !!}
@endsection