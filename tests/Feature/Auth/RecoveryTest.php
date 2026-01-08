<?php

/**
 * Название: RecoveryTest
 * Дата-время: 07-01-2026 17:15
 * Описание: Комплексное тестирование восстановления пароля.
 * Проверяет генерацию OTP, логику миграции (без телефона) и 
 * обязательную отправку нового пароля на email после сброса.
 */

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;
use Mockery;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// --- ШАГ 1: ЗАПРОС КОДА ---

test('пользователь с верными данными получает код на почту', function () {
    $user = User::factory()->create([
        'email' => 'user@grifmaster.ru',
        'phone' => '+7 (999) 111-22-33',
    ]);

    $mock = Mockery::mock('alias:'.MailService::class);
    // Проверяем, что на первом шаге уходит письмо с кодом
    $mock->shouldReceive('send')
        ->withArgs(fn($to, $subject, $body) => $to === 'user@grifmaster.ru' && str_contains($body, 'Ваш код'))
        ->once()
        ->andReturn(true);

    $response = $this->post(route('recovery.send'), [
        'email' => 'user@grifmaster.ru',
        'phone' => '+7 (999) 111-22-33',
    ]);

    $response->assertRedirect(route('recovery.verify.form'));
    $this->assertDatabaseHas('b2b_users_recovery', ['email' => 'user@grifmaster.ru']);
    
    Mockery::close();
});

test('пользователь (без номера в БД) может сбросить пароль только по email', function () {
    $user = User::factory()->create([
        'email' => 'migrated@grifmaster.ru',
        'phone' => null,
    ]);

    $mock = Mockery::mock('alias:'.MailService::class);
    $mock->shouldReceive('send')->andReturn(true);

    $response = $this->post(route('recovery.send'), [
        'email' => 'migrated@grifmaster.ru',
        'phone' => '+7 (000) 000-00-00',
    ]);

    $response->assertRedirect(route('recovery.verify.form'));
    $this->assertDatabaseHas('b2b_users_recovery', ['email' => 'migrated@grifmaster.ru']);
    
    Mockery::close();
});

test('система выдает ошибку, если телефон не совпадает с базой', function () {
    $user = User::factory()->create([
        'email' => 'secure@grifmaster.ru',
        'phone' => '+7 (900) 111-11-11',
    ]);

    $response = $this->post(route('recovery.send'), [
        'email' => 'secure@grifmaster.ru',
        'phone' => '+7 (000) 000-00-00',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertDatabaseMissing('b2b_users_recovery', ['email' => 'secure@grifmaster.ru']);
});

// --- ШАГ 2: ВЕРИФИКАЦИЯ И СБРОС ---

test('пароль успешно меняется и НОВЫЙ пароль отправляется на почту', function () {
    $oldPassword = 'old_password_123';
    $user = User::factory()->create([
        'email' => 'reset@grifmaster.ru',
        'password' => Hash::make($oldPassword),
    ]);

    // Имитируем наличие кода в базе
    DB::table('b2b_users_recovery')->insert([
        'email' => 'reset@grifmaster.ru',
        'code' => '654321',
        'expires_at' => now()->addMinutes(15),
    ]);

    session(['recovery_email' => 'reset@grifmaster.ru']);

    // ПРОВЕРКА ОТПРАВКИ ПАРОЛЯ:
    $mock = Mockery::mock('alias:'.MailService::class);
    $mock->shouldReceive('send')
        ->withArgs(function ($to, $subject, $body) {
            // Письмо должно содержать текст о новом пароле, а не код
            return $to === 'reset@grifmaster.ru' && 
                   str_contains($subject, 'Ваш новый пароль') && 
                   str_contains($body, 'Новый пароль:');
        })
        ->once()
        ->andReturn(true);

    $response = $this->post(route('recovery.verify.post'), [
        'email' => 'reset@grifmaster.ru',
        'code' => '654321',
    ]);

    $response->assertViewIs('auth.success');
    
    // Проверка смены хэша
    $user->refresh();
    $this->assertFalse(Hash::check($oldPassword, $user->password));
    
    Mockery::close();
});

test('система выдает ошибку при неверном коде подтверждения', function () {
    session(['recovery_email' => 'error@grifmaster.ru']);
    
    DB::table('b2b_users_recovery')->insert([
        'email' => 'error@grifmaster.ru',
        'code' => '123456',
        'expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->post(route('recovery.verify.post'), [
        'email' => 'error@grifmaster.ru',
        'code' => '000000',
    ]);

    $response->assertSessionHasErrors('code');
});