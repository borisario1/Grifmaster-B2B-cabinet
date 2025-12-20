<?php

/**
 * Название: LoginTest
 * Дата-время: 20-12-2025 23:58
 * Описание: Тестирование входа в систему и защиты закрытых разделов (Middleware).
 */

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('зарегистрированный пользователь может войти в систему под своими данными', function () {
    // Подготовка: создаем пользователя
    $user = User::factory()->create([
        'email' => 'login@grifmaster.ru',
        'password' => bcrypt('password123'),
    ]);

    // Действие: отправка формы логина
    $response = $this->post('/login', [
        'email' => 'login@grifmaster.ru',
        'password' => 'password123',
    ]);

    // Проверка: вход выполнен и редирект в кабинет
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('гость не может попасть в личный кабинет и перенаправляется на страницу логина', function () {
    // Пытаемся зайти на закрытый маршрут
    $response = $this->get('/dashboard');

    // Проверяем редирект на логин
    $response->assertRedirect('/login');
});