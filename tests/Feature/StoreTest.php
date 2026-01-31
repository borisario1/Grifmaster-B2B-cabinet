<?php

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
    $this->withoutMiddleware();
    
    $this->user = User::factory()->create();
    UserProfile::create(['user_id' => $this->user->id]);
    
    $this->org = Organization::factory()->create([
        'user_id' => $this->user->id,
        'inn' => '1234567890'
    ]);
    
    $this->user->update(['selected_org_id' => $this->org->id]);

    // Создаем тестовые продукты
    $this->product = Product::factory()->create([
        'name' => 'Test Product',
        'brand' => 'TestBrand',
        'price' => 1000,
        'is_active' => true
    ]);
});

test('удаленный сервер возвращает страницу каталога с товарами', function () {
    $response = $this->actingAs($this->user)->get(route('catalog.index'));

    $response->assertStatus(200);
    $response->assertViewHas('products');
    $response->assertSee('Test Product');
});

test('система корректно отображает флаги in_cart, is_liked, is_in_wishlist', function () {
    // Добавляем в корзину
    CartItem::create([
        'user_id' => $this->user->id,
        'org_id' => $this->org->id,
        'product_id' => $this->product->id,
        'qty' => 5
    ]);

    // Добавляем лайк и вишлист
    DB::table('b2b_product_interactions')->insert([
        ['user_id' => $this->user->id, 'product_id' => $this->product->id, 'type' => 'like', 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $this->user->id, 'product_id' => $this->product->id, 'type' => 'wishlist', 'created_at' => now(), 'updated_at' => now()]
    ]);

    $response = $this->actingAs($this->user)->get(route('catalog.index'));

    // Получаем коллекцию продуктов из view
    $products = $response->viewData('products');
    $productItem = $products->firstWhere('id', $this->product->id);

    expect($productItem->in_cart)->toBeTrue()
        ->and($productItem->cart_qty)->toBe(5)
        ->and($productItem->is_liked)->toBeTrue()
        ->and($productItem->is_in_wishlist)->toBeTrue();
});

test('система правильно рассчитывает цену с учетом скидки партнера', function () {
    // Устанавливаем скидку для бренда
    DB::table('b2b_discounts')->insert([
        'user_id' => $this->user->id,
        'brand' => 'TestBrand',
        'discount_percent' => 10,
        'created_at' => now(), 
        'updated_at' => now()
    ]);

    $response = $this->actingAs($this->user)->get(route('catalog.index'));
    
    $products = $response->viewData('products');
    $productItem = $products->firstWhere('id', $this->product->id);

    // Цена 1000, скидка 10% -> 900
    expect($productItem->discount_percent)->toBe(10)
        ->and($productItem->partner_price)->toBe(900.0);
});

test('страница избранного отображает только добавленные в вишлист товары', function () {
    DB::table('b2b_product_interactions')->insert([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'type' => 'wishlist',
        'created_at' => now(), 
        'updated_at' => now()
    ]);

    $response = $this->actingAs($this->user)->get(route('catalog.wishlist'));

    $response->assertStatus(200);
    $response->assertSee('Test Product');
    $response->assertViewHas('isWishlist', true);
});

test('страница понравившихся товаров отображает лайкнутые товары', function () {
    DB::table('b2b_product_interactions')->insert([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'type' => 'like',
        'created_at' => now(),
        'updated_at' => now()
    ]);

    $response = $this->actingAs($this->user)->get(route('catalog.liked'));

    $response->assertStatus(200);
    $response->assertSee('Test Product');
    $response->assertViewHas('isLiked', true);
});

test('страница ранее заказанных товаров отображает товары из истории заказов', function () {
    // Создаем заказ
    $orderId = DB::table('b2b_orders')->insertGetId([
        'user_id' => $this->user->id,
        'org_id' => $this->org->id,
        'order_code' => 'ORD-TEST',
        'total_amount' => 1000,
        'total_items' => 1,
        'status' => 'new',
        'created_at' => now(),
        'updated_at' => now()
    ]);

    DB::table('b2b_order_items')->insert([
        'order_id' => $orderId,
        'product_id' => $this->product->id,
        'article' => $this->product->article ?? 'ART-TEST',
        'name' => $this->product->name,
        'qty' => 1,
        'price' => 1000,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('catalog.ordered'));

    $response->assertStatus(200);
    $response->assertSee('Test Product');
    $response->assertViewHas('isOrdered', true);
});
