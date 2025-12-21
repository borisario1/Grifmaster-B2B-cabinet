<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('зарегистрированный пользователь может войти в систему под своими данными', function () {
    // 1. Создаем пользователя и профиль
    $user = User::factory()->create([
        'email' => 'login@grifmaster.ru',
        'password' => Hash::make('password123'),
    ]);
    
    UserProfile::create([
        'user_id'    => $user->id,
        'last_name'  => 'Тестов',
        'first_name' => 'Иван',
        'birth_date' => '1990-01-01'
    ]);

    // 2. Инициализируем сессию вручную, чтобы получить токен
    // Мы НЕ отключаем Middleware, поэтому сессия будет доступна в контроллере
    Session::start();
    
    // 3. Выполняем POST запрос
    $response = $this->post(route('login.post'), [
        '_token'   => csrf_token(), // Теперь csrf_token() возьмется из стартованной сессии
        'email'    => 'login@grifmaster.ru',
        'password' => 'password123',
    ]);

    // 4. Проверки
    $response->assertStatus(200);
    $response->assertViewIs('auth.success');
    $this->assertAuthenticatedAs($user);
});

test('гость не может попасть в личный кабинет и перенаправляется на логин', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect(route('login'));
});