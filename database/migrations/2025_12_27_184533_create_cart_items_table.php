<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b2b_cart_items', function (Blueprint $table) {
            $table->id();
            // Связь с пользователем
            $table->foreignId('user_id')->constrained('b2b_users')->onDelete('cascade');
            
            // Связь с организацией (может быть null, если покупает как физлицо)
            // Важно: если организация удаляется, корзина может либо очищаться, либо org_id становится null. 
            // Поставим set null для безопасности, но логика контроллера всё равно чистит корзину.
            $table->foreignId('org_id')->nullable()->constrained('b2b_organizations')->onDelete('cascade');
            
            $table->string('sku'); // Артикул товара
            $table->unsignedInteger('qty')->default(1);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_cart_items');
    }
};