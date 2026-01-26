<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b2b_products', function (Blueprint $table) {
            if (!Schema::hasColumn('b2b_products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('image_filename');
            }
        });
    }

    public function down(): void
    {
        Schema::table('b2b_products', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};