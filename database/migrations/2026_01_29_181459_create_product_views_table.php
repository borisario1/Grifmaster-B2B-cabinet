<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('b2b_product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('b2b_users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('b2b_products')->cascadeOnDelete();
            $table->timestamp('viewed_at')->useCurrent();
            $table->index(['user_id', 'viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_product_views');
    }
};