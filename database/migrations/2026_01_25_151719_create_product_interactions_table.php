<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b2b_product_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('b2b_users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('b2b_products')->cascadeOnDelete();
            $table->string('type')->index(); // 'like' или 'wishlist'
            $table->timestamps();

            // Уникальность: один юзер может поставить только 1 лайк одному товару
            $table->unique(['user_id', 'product_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_product_interactions');
    }
};