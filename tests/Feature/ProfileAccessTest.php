<?php

/**
 * Название: ProfileAccessTest
 * Дата-время: 21-12-2025 12:15
 * Описание: Тестирование Middleware 'check.profile'. 
 * Проверяет, что пользователи с неполными данными перенаправляются на страницу профиля.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Используем TestCase и RefreshDatabase для чистоты тестов
uses(TestCase::class, RefreshDatabase::class);

test('пользователь с незаполненной фамилией перенаправляется на страницу профиля', function () {
    // 1. Создаем пользователя
    $user = User::factory()->create();

    // 2. Имитируем вход и попытку зайти на дашборд
    // Используем алиас маршрута 'profile.edit', который мы создадим в следующем шаге
    $response = $this->actingAs($user)->get('/dashboard');

    // 3. Проверяем, что система сделала редирект (код 302) на страницу профиля
    $response->assertRedirect(route('profile.edit'));
    
    // 4. Проверяем, что в сессии появилось сообщение о необходимости заполнения
    $response->assertSessionHas('complete_required');
});

test('пользователь с заполненным профилем получает доступ к дашборду', function () {
    // 1. Создаем юзера
    $user = User::factory()->create();

    // 2. ЯВНО создаем ему профиль в нужной таблице
    \App\Models\UserProfile::create([
        'user_id'    => $user->id,
        'last_name'  => 'Иванов',
        'first_name' => 'Иван',
        'birth_date' => '1990-01-01',
    ]);

    // 3. Теперь пробуем зайти
    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200); // Теперь здесь будет 200, а не 302
});

test('контроллер профиля возвращает страницу успеха после обновления', function () {
    //Отключаем проверку CSRF (419 ошибка) через глобальный псевдоним.
    $this->withoutMiddleware(); 
    
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->post(route('profile.update'), [
        'last_name'  => 'Тестов',
        'first_name' => 'Тест',
        'birth_date' => '1990-01-01',
        'work_phone' => '79990000000',
    ]);

    $response->assertStatus(200);
    $response->assertViewIs('auth.success');
    $response->assertSee('Профиль обновлен!');
});

test('дашборд отображает ключевые группы меню для авторизованного пользователя', function () {
    // 1. Создаем простого пользователя
    $user = \App\Models\User::factory()->create();

    // 2. СОЗДАЕМ ЕМУ ПРОФИЛЬ в отдельной таблице b2b_user_profile
    // Именно наличие этой записи проверяет Middleware 'check.profile'
    \App\Models\UserProfile::create([
        'user_id'    => $user->id,
        'last_name'  => 'Иванов',
        'first_name' => 'Иван',
        'birth_date' => '1990-01-01',
    ]);

    // 3. Теперь заходим на дашборд
    $response = $this->actingAs($user)->get('/dashboard');

    // Теперь статус будет 200, так как Middleware нашел профиль
    $response->assertStatus(200);
    
    // Проверяем наличие заголовков блоков (из твоего b2b_menu.php)
    $response->assertSee('Заказы и каталог');
    $response->assertSee('Бизнес-инструменты');
    $response->assertSee('Настройки и сервисы');

    // Проверяем наличие конкретных пунктов меню
    $response->assertSee('Каталог');
    $response->assertSee('Прайс-листы');
    $response->assertSee('Мой профиль');
});