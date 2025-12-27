<?php

/**
 * Название: ProfileSettingsTest
 * Дата-время: 27-12-2025 20:35
 * Описание: Тестирование применения настроек профиля пользователя.
 * Проверяет дефолтные значения и корректность переключения каждого из 4х тумблеров через AJAX.
 */

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// Этот блок выполняется перед КАЖДЫМ тестом в этом файле
beforeEach(function () {
    // Отключаем защиту CSRF, чтобы не получать ошибку 419 в тестах.
    // Auth middleware при этом работает, так как мы используем actingAs
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('все настройки уведомлений по умолчанию ВКЛЮЧЕНЫ при создании профиля', function () {
    $user = User::factory()->create();
    $profile = UserProfile::create(['user_id' => $user->id]);
    
    // ВАЖНО: refresh() заставляет модель перечитать данные из БД.
    // Без этого PHP не знает, что MySQL подставил true в дефолтные поля.
    $profile->refresh();

    // Проверяем сразу все поля, существующие в миграции
    expect($profile->notify_general)->toBeTrue()
        ->and($profile->notify_news)->toBeTrue()
        ->and($profile->notify_orders)->toBeTrue()
        ->and($profile->notify_ticket)->toBeTrue();
});

test('каждая настройка корректно переключается', function (string $settingName) {
    // 1. Создаем пользователя
    $user = User::factory()->create();
    UserProfile::create(['user_id' => $user->id]);

    // 2. Отправляем запрос на выключение конкретной настройки ($settingName)
    $response = $this->actingAs($user)
        ->postJson(route('profile.notify'), [
            'name'  => $settingName, // Подставляется из dataset
            'value' => 0
        ]);

    // 3. Проверяем успех
    $response->assertStatus(200)
             ->assertJson(['success' => true]);

    // 4. Проверяем, что в БД изменилось именно это поле
    $this->assertDatabaseHas('b2b_user_profile', [
        'user_id'    => $user->id,
        $settingName => 0, // false
    ]);

})->with([
    'notify_general',
    'notify_news',
    'notify_orders',
    'notify_ticket',
]); 
// ^ Pest прогонит этот тест 4 раза, подставляя каждое значение в $settingName

test('нельзя отключить уведомления от менеджера (защищенное поле)', function () {
    $user = User::factory()->create();
    UserProfile::create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->postJson(route('profile.notify'), [
            'name'  => 'notify_manager', // Пытаемся изменить запрещенное поле
            'value' => 0
        ]);

    $response->assertStatus(422); // Сервер должен отказать
});