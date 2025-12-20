<?php

/**
 * Название: OrganizationTest
 * Дата-время: 20-12-2025 23:55
 * Описание: Тестирование корректности создания организаций и их
 * связи с пользователями через таблицу b2b_organizations.
 */

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('организация успешно создается и корректно привязывается к пользователю в b2b_organizations', function () {
    // Создаем пользователя через фабрику
    $user = User::factory()->create();

    // Создаем организацию, связанную с этим пользователем
    $organization = Organization::create([
        'user_id' => $user->id,
        'name'    => 'ООО ГРИФМАСТЕР',
        'inn'     => '1234567890',
        'type'    => 'org',
    ]);

    // Проверяем атрибуты модели
    $this->assertEquals('ООО ГРИФМАСТЕР', $organization->name);
    
    // Проверяем физическое наличие записи в таблице b2b_organizations
    $this->assertDatabaseHas('b2b_organizations', [
        'inn'     => '1234567890',
        'user_id' => $user->id
    ]);
});