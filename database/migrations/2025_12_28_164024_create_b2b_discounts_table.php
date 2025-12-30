<?php

/**
 * Название: xxxx_xx_xx_xxxxxx_create_b2b_discounts_table.php
 * Дата-время: 28-12-2025 17:50
 * Описание: Создание таблицы персональных скидок пользователей.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b2b_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('b2b_users')->onDelete('cascade');
            
            // Поля из твоей логики Store.php
            $table->string('brand', 100)->nullable()->index();
            $table->string('collection', 100)->nullable()->index();
            $table->string('product_type', 100)->nullable()->index();
            $table->string('product_category', 100)->nullable()->index();
            
            $table->integer('discount_percent')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_discounts');
    }
};