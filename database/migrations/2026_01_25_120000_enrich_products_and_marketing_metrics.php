<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Таблица товаров (b2b_products)
        Schema::table('b2b_products', function (Blueprint $table) {
            if (!Schema::hasColumn('b2b_products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('status');
            }
            if (!Schema::hasColumn('b2b_products', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_featured');
            }
            if (!Schema::hasColumn('b2b_products', 'min_quantity')) {
                $table->decimal('min_quantity', 15, 3)->default(1.000)->after('price');
            }
        });

        // 2. Таблица деталей (b2b_product_details)
        Schema::table('b2b_product_details', function (Blueprint $table) {
            // Контент из Webasyst [cite: 1, 2, 4]
            if (!Schema::hasColumn('b2b_product_details', 'summary')) {
                $table->text('summary')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('b2b_product_details', 'features')) {
                $table->json('features')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('b2b_product_details', 'images')) {
                $table->json('images')->nullable()->after('features');
            }
            if (!Schema::hasColumn('b2b_product_details', 'last_enriched_at')) {
                $table->timestamp('last_enriched_at')->nullable();
            }

            // Маркетинговые метрики
            if (!Schema::hasColumn('b2b_product_details', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('likes_count');
            }
            if (!Schema::hasColumn('b2b_product_details', 'add_to_cart_count')) {
                $table->unsignedInteger('add_to_cart_count')->default(0)->after('views_count');
            }
            if (!Schema::hasColumn('b2b_product_details', 'orders_count')) {
                $table->unsignedInteger('orders_count')->default(0)->after('add_to_cart_count');
            }
            if (!Schema::hasColumn('b2b_product_details', 'wishlist_count')) {
                $table->unsignedInteger('wishlist_count')->default(0)->after('orders_count');
            }
            if (!Schema::hasColumn('b2b_product_details', 'last_viewed_at')) {
                $table->timestamp('last_viewed_at')->nullable()->after('wishlist_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('b2b_products', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'sort_order', 'min_quantity']);
        });

        Schema::table('b2b_product_details', function (Blueprint $table) {
            $table->dropColumn([
                'summary', 'features', 'images', 'last_enriched_at',
                'views_count', 'add_to_cart_count', 'orders_count', 
                'wishlist_count', 'last_viewed_at'
            ]);
        });
    }
};