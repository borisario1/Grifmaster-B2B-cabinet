<?php

/**
 * Название: RegistrationTest
 * Дата-время: 21-12-2025 00:10
 * Описание: Полный цикл тестирования регистрации с перехватом почтового сервиса.
 */

namespace Tests\Feature\Auth;

use App\Services\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Отключает вообще все проверки CSRF для этого файла тестов
    $this->withoutMiddleware(); 
});

test('пользователь может инициировать регистрацию и данные сохраняются во временной таблице', function () {
    /**
     * ГАРАНТИРОВАННЫЙ MOCK:
     * Мы заменяем реализацию MailService в контейнере Laravel.
     * Теперь при вызове MailService::send() внутри контроллера, 
     * Laravel вернет true мгновенно без выполнения cURL.
     */
    $mock = Mockery::mock('alias:'.MailService::class);
    $mock->shouldReceive('send')->andReturn(true);

    // 1. Отправляем данные
    $response = $this->post('/register_action', [
        'email' => 'test@grifmaster.ru',
        'phone' => '+7 (999) 000-00-00',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    // 2. Проверяем редирект (теперь он сработает, так как 'send' вернул true)
    $response->assertRedirect(route('register.verify'));

    // 3. Проверяем БД
    $this->assertDatabaseHas('b2b_users_temp', [
        'email' => 'test@grifmaster.ru',
    ]);

    $this->assertEquals('test@grifmaster.ru', session('register_email'));
    
    Mockery::close();
});

test('пользователь успешно завершает регистрацию при вводе верного кода подтверждения', function () {
    // Подготовка данных
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

    // Проверка создания в основной таблице
    $this->assertDatabaseHas('b2b_users', [
        'email' => 'verify@grifmaster.ru',
        'role' => 'partner',
        'status' => 'active',
    ]);

    $this->assertDatabaseMissing('b2b_users_temp', [
        'email' => 'verify@grifmaster.ru',
    ]);

    $this->assertAuthenticated();
});