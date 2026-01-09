<?php

/**
 * Название: OrganizationTest
 * Дата-время: 08-01-2026 19:00
 * Описание: Feature-тесты для модуля организаций.
 */

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Organization;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

function createUserWithProfile() {
    $user = User::factory()->create();
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
    $this->get(route('organizations.index'))->assertRedirect(route('login'));
});

test('авторизованный пользователь видит свои организации', function () {
    $user = createUserWithProfile();
    $org = Organization::factory()->create(['user_id' => $user->id, 'name' => 'Моя Компания', 'inn' => '7700000000']);

    $this->actingAs($user)->get(route('organizations.index'))
        ->assertStatus(200)
        ->assertSee('Моя Компания')
        ->assertSee('7700000000');
});

// --- БЛОК 2: СОЗДАНИЕ ---

test('нельзя создать организацию без ИНН', function () {
    $user = createUserWithProfile();
    $this->actingAs($user)->post(route('organizations.store'), ['name' => 'Тест'])->assertSessionHasErrors('inn');
});

test('при успешном создании данные пишутся в БД', function () {
    $user = createUserWithProfile();
    $dadataJson = json_encode(['name' => ['full_with_opf' => 'Полное ООО'], 'state' => ['status' => 'ACTIVE'], 'address' => ['value' => 'Москва']]);

    $this->actingAs($user)->post(route('organizations.store'), [
        'inn' => '7712345678',
        'name' => 'ООО Вектор',
        'dadata_raw' => $dadataJson
    ])->assertRedirect(route('organizations.index'));

    $this->assertDatabaseHas('b2b_organizations', ['user_id' => $user->id, 'inn' => '7712345678']);
});

// --- БЛОК 3: БИЗНЕС-ЛОГИКА ---

test('выбор организации обновляет профиль пользователя', function () {
    $user = createUserWithProfile();
    $org = Organization::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get(route('organizations.select', $org->id))->assertRedirect();
    
    $this->assertDatabaseHas('b2b_users', ['id' => $user->id, 'selected_org_id' => $org->id]);
});

/**
 * ОБНОВЛЕНО: Теперь проверяем, что корзина НЕ очищается.
 * Это подтверждает работу "умной корзины", разделенной по org_id.
 */
test('смена организации сохраняет товары в базе данных', function () {
    $user = createUserWithProfile();
    $org1 = Organization::factory()->create(['user_id' => $user->id]);
    $org2 = Organization::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create();

    $user->update(['selected_org_id' => $org1->id]);

    // Добавляем товар для первой организации
    DB::table('b2b_cart_items')->insert([
        'user_id'    => $user->id,
        'product_id' => $product->id,
        'qty'        => 5,
        'org_id'     => $org1->id,
        'created_at' => now()
    ]);

    // Переключаемся на вторую организацию
    $this->actingAs($user)->get(route('organizations.select', $org2->id));

    // ПРОВЕРКА: Товар для первой организации должен ОСТАТЬСЯ в базе
    $this->assertDatabaseHas('b2b_cart_items', [
        'user_id'    => $user->id,
        'org_id'     => $org1->id,
        'product_id' => $product->id
    ]);
    
    // ПРОВЕРКА: Активная организация изменилась
    $this->assertEquals($org2->id, $user->fresh()->selected_org_id);
});