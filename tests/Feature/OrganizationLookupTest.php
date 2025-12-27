<?php

/**
 * Название: OrganizationLookupTest
 * Дата-время: 28-12-2025 02:15
 * Описание: Тестирование поиска.
 */

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Organization;
use App\Models\OrganizationInfo;
use App\Services\DadataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;
use Mockery\MockInterface;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Отключаем только CSRF, чтобы API-запросы проходили
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// Хелпер: Создаем юзера С ЗАПОЛНЕННЫМ профилем
function createLookupUser() {
    $user = User::factory()->create();
    
    // Важно: заполняем поля, чтобы Middleware 'check.profile' пропустил нас
    UserProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Tester',
        'last_name' => 'Lookupov',
        'birth_date' => '2000-01-01'
    ]);
    
    return $user;
}

test('Если организация есть в БД, берем из кэша и НЕ вызываем Dadata', function () {
    $user = createLookupUser();
    $inn = '7700000000';

    // 1. ПОДГОТОВКА: Создаем запись в БД
    $org = Organization::factory()->create([
        'user_id' => User::factory()->create()->id, // Любой владелец
        'inn' => $inn,
        'name' => 'ООО Из Кэша'
    ]);

    OrganizationInfo::create([
        'organization_id' => $org->id,
        'name_full' => 'ООО Из Кэша Полное',
        'status' => 'ACTIVE',
        'dadata_raw' => ['test_marker' => 'found_in_db']
    ]);

    // 2. MOCK: Запрещаем вызов сервиса
    $this->mock(DadataService::class, function (MockInterface $mock) {
        $mock->shouldReceive('findByInn')->never();
    });

    // 3. ДЕЙСТВИЕ: POST запрос (как на фронте)
    $response = $this->actingAs($user)
                     ->postJson(route('organizations.lookup'), ['inn' => $inn]);

    // 4. ПРОВЕРКА
    $response->assertStatus(200)
             ->assertJson(['ok' => true, 'source' => 'local'])
             ->assertJsonPath('suggestions.0.data.test_marker', 'found_in_db');
});

test('Если организации нет в БД, вызываем DadataService', function () {
    $user = createLookupUser();
    $inn = '9999999999';

    // 1. Фейковый ответ
    $fakeSuggestions = [
        [
            'value' => 'ООО Новая Организация',
            'unrestricted_value' => 'ООО Новая Организация',
            'data' => [
                'inn' => $inn,
                'state' => ['status' => 'ACTIVE']
            ]
        ]
    ];

    // 2. MOCK: Ожидаем вызов
    $this->mock(DadataService::class, function (MockInterface $mock) use ($inn, $fakeSuggestions) {
        $mock->shouldReceive('findByInn')
             ->once()
             ->with($inn)
             ->andReturn($fakeSuggestions);
    });

    // 3. ДЕЙСТВИЕ
    $response = $this->actingAs($user)
                     ->postJson(route('organizations.lookup'), ['inn' => $inn]);

    // 4. ПРОВЕРКА
    $response->assertStatus(200)
             ->assertJson(['ok' => true, 'source' => 'dadata'])
             ->assertJsonPath('suggestions.0.value', 'ООО Новая Организация');
});