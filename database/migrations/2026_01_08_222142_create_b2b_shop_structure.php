<?php

/**
 * Название: CreateB2BShopStructure
 * Описание: Объединенная миграция магазина (Корзина, Заказы, Детали товаров)
 * Дата: 08-01-2026
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. КОРЗИНА
        Schema::create('b2b_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('b2b_users')->onDelete('cascade');
            $table->foreignId('org_id')->nullable()->constrained('b2b_organizations')->onDelete('set null');
            $table->foreignId('product_id')->constrained('b2b_products')->onDelete('cascade');
            $table->integer('qty')->default(1);
            $table->timestamps();
            
            // Уникальный индекс, чтобы не плодить дубли одного товара в одной корзине
            $table->unique(['user_id', 'org_id', 'product_id'], 'cart_item_unique');
        });

        // 2. ЗАКАЗЫ
        Schema::create('b2b_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 20)->unique(); 
            $table->foreignId('user_id')->constrained('b2b_users');
            $table->foreignId('org_id')->nullable()->constrained('b2b_organizations')->onDelete('set null');
            
            // Snapshot данных организации на момент заказа
            $table->string('org_name')->nullable();
            $table->string('org_inn', 20)->nullable();
            
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 10)->default('₽'); 
            $table->string('status', 50)->default('new'); 
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // 3. СОСТАВ ЗАКАЗА
        Schema::create('b2b_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('b2b_orders')->onDelete('cascade');
            
            // Если товар удалят из каталога, в заказе он должен остаться для истории
            $table->foreignId('product_id')->nullable()->constrained('b2b_products')->onDelete('set null');
            
            $table->string('name'); 
            $table->string('article'); 
            $table->integer('qty');
            $table->decimal('price', 15, 2); 
            $table->timestamps();
        });

        // 4. ДЕТАЛИ ТОВАРА И ВЗАИМОДЕЙСТВИЯ
        Schema::create('b2b_product_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('b2b_products')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->json('attributes')->nullable(); 
            
            // Поля для лайков и рейтинга, чтобы не считать их каждый раз тяжелым SQL
            $table->integer('likes_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_product_details');
        Schema::dropIfExists('b2b_order_items');
        Schema::dropIfExists('b2b_orders');
        Schema::dropIfExists('b2b_cart_items');
    }
};