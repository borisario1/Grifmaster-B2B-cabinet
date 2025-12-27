<?php

/**
 * Название: RegistrationTest
 * Дата-время: 21-12-2025 22:30
 * Описание: Тестирование регистрации с учетом логики отказоустойчивости почты.
 */

namespace Tests\Feature\Auth;

use App\Services\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;
use Mockery;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // ВАЖНО: Отключаем ТОЛЬКО проверку токена.
    // Если отключить всё (withoutMiddleware()), то переменная $errors исчезнет из вьюх.
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('пользователь инициирует регистрацию, код уходит и данные сохраняются в temp', function () {
    // Имитируем успешную отправку почты
    $mock = Mockery::mock('alias:'.MailService::class);
    $mock->shouldReceive('send')->andReturn(true);

    $response = $this->post('/register_action', [
        'email' => 'standard@grifmaster.ru',
        'phone' => '+7 (000) 000-00-00',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    // Должен быть редирект на страницу ввода кода
    $response->assertRedirect(route('register.verify'));
    
    $this->assertDatabaseHas('b2b_users_temp', ['email' => 'standard@grifmaster.ru']);
    $this->assertEquals('standard@grifmaster.ru', session('register_email'));
    
    Mockery::close();
});

test('при ошибке отправки email пользователь создается сразу, но без верификации', function () {
    // Имитируем сбой почтового сервиса (возвращаем false)
    $mock = Mockery::mock('alias:'.MailService::class);
    $mock->shouldReceive('send')->andReturn(false);

    $response = $this->post('/register_action', [
        'email' => 'failmail@grifmaster.ru',
        'phone' => '+7 (111) 111-11-11',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    // Проверяем, что сработал finalizeRegistration:
    // 1. Статус 200 и вьюха успеха
    $response->assertStatus(200);
    $response->assertViewIs('auth.success');
    
    // 2. Пользователь создан в основной таблице
    $this->assertDatabaseHas('b2b_users', [
        'email' => 'failmail@grifmaster.ru',
        'email_verified_at' => null // Почта не подтверждена
    ]);

    // 3. Временная запись удалена
    $this->assertDatabaseMissing('b2b_users_temp', ['email' => 'failmail@grifmaster.ru']);
    
    $this->assertAuthenticated();
    Mockery::close();
});

test('пользователь успешно завершает регистрацию при вводе верного кода подтверждения', function () {
    // Подготовка временных данных (как после успешной отправки письма)
    DB::table('b2b_users_temp')->insert([
        'email' => 'verify@grifmaster.ru',
        'phone' => '123456789',
        'password_hash' => Hash::make('password123'),
        'code' => '123456',
        'created_at' => now(),
    ]);

    session(['register_email' => 'verify@grifmaster.ru']);

    // Верификация
    $response = $this->post('/verify_action', [
        'code' => '123456',
    ]);

    // Проверка создания в основной таблице (теперь finalizeRegistration должен сработать)
    $this->assertDatabaseHas('b2b_users', [
        'email' => 'verify@grifmaster.ru',
    ]);
});

test('страница ввода кода открывается корректно (GET /verify)', function () {
    // Имитируем, что в сессии есть email
    session(['register_email' => 'test@example.com']);

    // Теперь, когда middleware включены, $errors будет доступна во вьюхе
    $response = $this->get(route('register.verify')); 

    $response->assertStatus(200);
    $response->assertViewIs('auth.verify');
});