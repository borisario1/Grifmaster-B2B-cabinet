<?php

/**
 * Название: CartOrderTest
 * Дата-время: 08-01-2026 21:30
 * Описание: Тестирование корзины и заказов с учетом удаления связанных сущностей.
 */

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\UserProfile;
use App\Models\Organization;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Отключаем все Middleware, чтобы CSRF и проверки профиля не мешали логике тестов
    $this->withoutMiddleware();

    $this->user = User::factory()->create();
    UserProfile::create([
        'user_id'    => $this->user->id,
        'first_name' => 'Иван',
        'last_name'  => 'Тестов',
        'work_phone' => '+79001112233',
        'birth_date' => '1990-01-01'
    ]);

    $this->org1 = Organization::factory()->create([
        'user_id' => $this->user->id, 
        'name' => 'ООО Вектор',
        'inn' => '7712345678'
    ]);
    
    $this->org2 = Organization::factory()->create([
        'user_id' => $this->user->id, 
        'name' => 'ИП Петров',
        'inn' => '501234567890'
    ]);
    
    $this->product = Product::factory()->create(['price' => 5000]);
});

// --- БЛОК 1: КОРЗИНА ---

test('добавление товара и обновление количества через mode set/add', function () {
    $this->user->update(['selected_org_id' => $this->org1->id]);

    $response = $this->actingAs($this->user)->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'qty' => 2,
        'mode' => 'add'
    ]);

    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('b2b_cart_items', ['qty' => 2, 'org_id' => $this->org1->id]);
});

test('установка количества в 0 удаляет товар из корзины', function () {
    $this->user->update(['selected_org_id' => $this->org1->id]);
    
    CartItem::create([
        'user_id' => $this->user->id,
        'org_id' => $this->org1->id,
        'product_id' => $this->product->id,
        'qty' => 5
    ]);

    $this->actingAs($this->user)->post(route('cart.add'), [
        'product_id' => $this->product->id,
        'qty' => 0,
        'mode' => 'set'
    ])->assertJson(['action' => 'removed']);

    $this->assertDatabaseMissing('b2b_cart_items', ['product_id' => $this->product->id]);
});

test('корзина НЕ очищается при смене организации', function () {
    $this->user->update(['selected_org_id' => $this->org1->id]);
    CartItem::create(['user_id' => $this->user->id, 'org_id' => $this->org1->id, 'product_id' => $this->product->id, 'qty' => 1]);

    // Эмуляция выбора другой организации
    $this->actingAs($this->user)->get(route('organizations.select', $this->org2->id));

    $this->assertDatabaseHas('b2b_cart_items', [
        'user_id' => $this->user->id,
        'org_id' => $this->org1->id
    ]);
});

// --- БЛОК 2: ЗАКАЗЫ И УДАЛЕНИЕ ---

test('чекаут успешно создает заказ и очищает корзину текущей организации', function () {
    $this->user->update(['selected_org_id' => $this->org1->id]);
    CartItem::create(['user_id' => $this->user->id, 'org_id' => $this->org1->id, 'product_id' => $this->product->id, 'qty' => 1]);

    $response = $this->actingAs($this->user)->post(route('cart.checkout'));
    
    $order = DB::table('b2b_orders')->where('user_id', $this->user->id)->first();
    
    $this->assertNotNull($order);
    $response->assertStatus(302); // Редирект на success
    $this->assertDatabaseMissing('b2b_cart_items', ['org_id' => $this->org1->id]);
});

test('данные плательщика сохраняются в заказе даже после удаления организации', function () {
    $this->user->update(['selected_org_id' => $this->org1->id]);
    CartItem::create(['user_id' => $this->user->id, 'org_id' => $this->org1->id, 'product_id' => $this->product->id, 'qty' => 1]);

    // Создаем заказ через контроллер
    $this->actingAs($this->user)->post(route('cart.checkout'));
    $order = DB::table('b2b_orders')->where('user_id', $this->user->id)->first();

    // Удаляем организацию
    $this->org1->delete(); 

    $response = $this->actingAs($this->user)->get(route('orders.show', $order->order_code));

    $response->assertStatus(200);
    $response->assertSee('ООО Вектор');
    $response->assertSee('7712345678');
    $response->assertSee('организация удалена'); 
});

test('данные физлица видны в заказе даже после удаления профиля', function () {
    // 1. Создаем заказ физлица (snapshot)
    $orderCode = 'PHYS-DEL-123';
    DB::table('b2b_orders')->insert([
        'order_code' => $orderCode,
        'user_id' => $this->user->id,
        'org_id' => null,
        'total_amount' => 1000,
        'total_items' => 1,
        'status' => 'new',
        'created_at' => now()
    ]);

    // 2. Удаляем профиль
    DB::table('b2b_user_profile')->where('user_id', $this->user->id)->delete();

    // 3. Заходим в список заказов
    $response = $this->actingAs($this->user)->get(route('orders.index'));

    $response->assertStatus(200);
    $response->assertSee('профиль удален');
});

test('если корзина не пуста, в списке заказов отображается блок напоминания', function () {
    $this->user->update(['selected_org_id' => $this->org1->id]);
    CartItem::create(['user_id' => $this->user->id, 'org_id' => $this->org1->id, 'product_id' => $this->product->id, 'qty' => 1]);

    $response = $this->actingAs($this->user)->get(route('orders.index'));

    $response->assertStatus(200);
    $response->assertSee('У вас есть незавершенный заказ');
    $response->assertSee('Перейти в корзину');
});