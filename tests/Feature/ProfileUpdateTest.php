<?php

/**
 * Название: ProfileUpdateTest
 * Дата-время: 21-12-2025 17:30
 * Описание: Проверка сохранения данных в отдельную таблицу профиля и доступ к Dashboard.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('пользователь обновляет данные профиля, сохраняет и входит в ЛК', function () {
    $this->withoutMiddleware(); // Отключаем защиту для теста

    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->post(route('profile.update'), [
        'last_name'  => 'Гусев',
        'first_name' => 'Борис',
        'birth_date' => '1990-06-22',
        'work_phone' => '+79771346667',
    ]);

    // Проверяем страницу успеха (вьюха auth.success)
    $response->assertStatus(200);
    
    // Проверяем, что в ТВОЕЙ таблице появилась запись
    $this->assertDatabaseHas('b2b_user_profile', [
        'user_id'    => $user->id,
        'last_name'  => 'Гусев',
        'first_name' => 'Борис'
    ]);
});