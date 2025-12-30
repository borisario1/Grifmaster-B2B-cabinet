<?php

/**
 * Название: 2025_12_28_163104_create_products_table.php
 * Дата-время: 28-12-2025 16:35
 * Описание: Создание таблицы товаров (каталога) на основе данных из 1С.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b2b_products', function (Blueprint $table) {
            $table->id();
            $table->string('code_1c', 50)->index(); // Код из 1С
            $table->string('article', 100)->unique(); // Артикул (ключ для синхронизации)
            $table->string('name');
            $table->string('brand', 100)->index();
            $table->string('product_type')->nullable();
            $table->string('product_category')->nullable();
            $table->string('collection')->nullable();
            $table->integer('free_stock')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->string('currency', 10)->default('₽');
            $table->string('status', 50)->nullable(); // Сток / Вывод
            $table->string('barcode', 100)->nullable();
            $table->string('image_filename')->nullable();
            
            // Техническое поле для связи со скидками (если решим делать группы)
            $table->string('discount_group')->nullable()->index();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};