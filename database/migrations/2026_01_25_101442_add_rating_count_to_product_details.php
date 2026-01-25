<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b2b_product_details', function (Blueprint $table) {
            if (!Schema::hasColumn('b2b_product_details', 'rating_count')) {
                // Добавляем поле после 'rating'
                $table->unsignedInteger('rating_count')->default(0)->after('rating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('b2b_product_details', function (Blueprint $table) {
            $table->dropColumn('rating_count');
        });
    }
};