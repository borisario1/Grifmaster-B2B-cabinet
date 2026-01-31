<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // $this->withoutMiddleware(); // Ошибка: отключает сессии и ощибки
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    
    $this->user = User::factory()->create();
    // Создаем организацию, так как layout может ее требовать
    $this->org = \App\Models\Organization::factory()->create(['user_id' => $this->user->id]);
    $this->user->update(['selected_org_id' => $this->org->id]);
    
    // Создаем профиль, чтобы пройти middleware check.profile
    \App\Models\UserProfile::create([
        'user_id' => $this->user->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'work_phone' => '+70000000000',
        'birth_date' => '1990-01-01',
    ]);
});

test('пользователь видит список своих обращений', function () {
    Ticket::create([
        'user_id' => $this->user->id,
        'request_code' => 'REQ-001',
        'topic' => 'Тестовая тема',
        'message' => 'Сообщение',
        'category' => 'Общие вопросы',
        'status' => 'new'
    ]);

    $response = $this->actingAs($this->user)->get(route('tickets.index'));

    $response->assertStatus(200);
    $response->assertSee('Тестовая тема');
    $response->assertSee('REQ-001');
});

test('пользователь может открыть форму создания обращения', function () {
    $response = $this->actingAs($this->user)->get(route('tickets.create'));
    $response->assertStatus(200);
    $response->assertSee('Новое обращение');
});

test('пользователь успешно создает новое обращение', function () {
    $data = [
        'topic' => 'Проблема с заказом',
        'category' => 'Заказы',
        'message' => 'Где мой заказ?',
    ];

    $response = $this->actingAs($this->user)->post(route('tickets.store'), $data);

    $response->assertStatus(302); // redirect to show
    $this->assertDatabaseHas('b2b_requests', [
        'user_id' => $this->user->id,
        'topic' => 'Проблема с заказом'
    ]);
});

test('система не дает создать обращение с невалидными данными', function () {
    $response = $this->actingAs($this->user)->post(route('tickets.store'), []); // Пустые данные
    $response->assertStatus(302);
    $response->assertSessionHasErrors(['topic', 'message', 'category']);
});

test('пользователь может просматривать детали обращения', function () {
    $ticket = Ticket::create([
        'user_id' => $this->user->id,
        'request_code' => 'REQ-VIEW',
        'topic' => 'Детали',
        'message' => 'Сообщение',
        'category' => 'Общие',
        'status' => 'new'
    ]);

    $response = $this->actingAs($this->user)->get(route('tickets.show', $ticket->request_code));

    $response->assertStatus(200);
    $response->assertSee('REQ-VIEW');
});

test('пользователь не может смотреть чужие обращения', function () {
    $otherUser = User::factory()->create();
    $ticket = Ticket::create([
        'user_id' => $otherUser->id,
        'request_code' => 'REQ-ALIEN',
        'topic' => 'Чужое',
        'message' => 'Сообщение',
        'category' => 'Общие',
        'status' => 'new'
    ]);

    $response = $this->actingAs($this->user)->get(route('tickets.show', $ticket->request_code));
    
    $response->assertStatus(404);
});

test('пользователь может отправить сообщение в тикет', function () {
    $ticket = Ticket::create([
        'user_id' => $this->user->id,
        'request_code' => 'REQ-MSG',
        'topic' => 'Чат',
        'message' => 'Первое',
        'category' => 'Общие',
        'status' => 'open'
    ]);

    $response = $this->actingAs($this->user)->post(route('tickets.message', $ticket->request_code), [
        'message' => 'Второе сообщение'
    ]);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('b2b_request_messages', [
        'request_id' => $ticket->id,
        'message' => 'Второе сообщение',
        'sender_type' => 'user'
    ]); // Assuming table name, usually it is linked via migration
});

test('пользователь может закрыть обращение', function () {
    $ticket = Ticket::create([
        'user_id' => $this->user->id,
        'request_code' => 'REQ-CLOSE',
        'topic' => 'Закрытие',
        'message' => 'Текст',
        'category' => 'Общие',
        'status' => 'open'
    ]);

    $this->actingAs($this->user)->get(route('tickets.close', $ticket->request_code));

    $ticket->refresh();
    expect($ticket->status)->toBe('closed');
});
