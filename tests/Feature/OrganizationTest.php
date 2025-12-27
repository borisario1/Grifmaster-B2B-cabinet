<?php

/**
 * Название: OrganizationTest
 * Дата-время: 27-12-2025 22:30
 * Описание: Feature-тесты для модуля организаций.
 * * Покрывает следующие сценарии:
 * 1. Безопасность (Security): Проверка прав доступа (свой/чужой/гость).
 * 2. Валидация (Validation): Нельзя создать без обязательных полей.
 * 3. Интеграция (Integration): Сохранение данных в БД (основная таблица + Info).
 * 4. Бизнес-логика (Business Logic): 
 * - Запись выбранной организации в профиль пользователя.
 * - Автоматическая очистка корзины при смене организации.
 */

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// Отключаем CSRF, чтобы не ловить 419
beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// Вспомогательная функция для создания юзера С ПРОФИЛЕМ
function createUserWithProfile() {
    $user = User::factory()->create();
    
    // Создаем профиль, чтобы Middleware пропустил нас к организациям
    UserProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'birth_date' => '2000-01-01'
    ]);
    
    return $user;
}

// --- БЛОК 1: ДОСТУП И ПРОСМОТР ---

test('гость не видит список организаций и редиректится на вход', function () {
    $response = $this->get(route('organizations.index'));
    $response->assertRedirect(route('login'));
});

test('авторизованный пользователь видит свои организации', function () {
    // Используем нашу функцию создания с профилем
    $user = createUserWithProfile();
    
    $org = Organization::factory()->create([
        'user_id' => $user->id, 
        'name' => 'Моя Компания',
        'inn' => '7700000000'
    ]);

    $response = $this->actingAs($user)->get(route('organizations.index'));

    $response->assertStatus(200);
    $response->assertSee('Моя Компания');
    $response->assertSee('7700000000');
});

test('пользователь НЕ может видеть организации другого пользователя', function () {
    $user1 = createUserWithProfile(); // Этому нужен профиль, чтобы делать запросы
    $user2 = User::factory()->create(); // Этому не обязательно, он просто владелец жертва
    
    $orgUser2 = Organization::factory()->create(['user_id' => $user2->id]);

    $response = $this->actingAs($user1)->get(route('organizations.show', $orgUser2->id));

    $response->assertStatus(403); 
});


// --- БЛОК 2: СОЗДАНИЕ ---

test('нельзя создать организацию без ИНН', function () {
    $user = createUserWithProfile();

    $response = $this->actingAs($user)->post(route('organizations.store'), [
        'name' => 'Тест без ИНН',
    ]);

    $response->assertSessionHasErrors('inn');
});

test('при успешном создании данные пишутся в БД', function () {
    $user = createUserWithProfile();

    $dadataJson = json_encode([
        'name' => ['full_with_opf' => 'Полное наименование ООО "Вектор"'],
        'state' => ['status' => 'ACTIVE'],
        'address' => ['value' => 'г. Москва, Кремль']
    ]);

    $response = $this->actingAs($user)->post(route('organizations.store'), [
        'inn' => '7712345678',
        'name' => 'ООО Вектор',
        'dadata_raw' => $dadataJson
    ]);

    $response->assertRedirect(route('organizations.index'));

    $this->assertDatabaseHas('b2b_organizations', [
        'user_id' => $user->id,
        'inn' => '7712345678',
    ]);

    $this->assertDatabaseHas('b2b_organization_infos', [
        'name_full' => 'Полное наименование ООО "Вектор"',
    ]);
});


// --- БЛОК 3: БИЗНЕС-ЛОГИКА ---

test('выбор организации обновляет профиль пользователя', function () {
    $user = createUserWithProfile();
    $org = Organization::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('organizations.select', $org->id));

    $response->assertRedirect();
    
    $this->assertDatabaseHas('b2b_users', [
        'id' => $user->id,
        'selected_org_id' => $org->id
    ]);
});

test('смена организации очищает корзину', function () {
    $user = createUserWithProfile();
    
    $org1 = Organization::factory()->create(['user_id' => $user->id]);
    $org2 = Organization::factory()->create(['user_id' => $user->id]);

    $user->update(['selected_org_id' => $org1->id]);

    DB::table('b2b_cart_items')->insert([
        'user_id' => $user->id,
        'sku' => 'TEST-ITEM-001',
        'qty' => 5,
        'org_id' => $org1->id
    ]);

    $this->assertDatabaseHas('b2b_cart_items', ['sku' => 'TEST-ITEM-001']);

    $this->actingAs($user)->get(route('organizations.select', $org2->id));

    $this->assertDatabaseMissing('b2b_cart_items', ['sku' => 'TEST-ITEM-001']);
    
    $this->assertDatabaseHas('b2b_users', [
        'id' => $user->id, 
        'selected_org_id' => $org2->id
    ]);
});