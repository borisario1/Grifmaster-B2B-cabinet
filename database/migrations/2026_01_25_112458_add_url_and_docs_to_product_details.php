<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b2b_product_details', function (Blueprint $table) {
            // Ссылка на товар (чпу) для кнопки "Смотреть на сайте"
            if (!Schema::hasColumn('b2b_product_details', 'url_slug')) {
                $table->string('url_slug')->nullable()->after('product_id');
            }
            // JSON поле для хранения массива документов (инструкции, сертификаты)
            if (!Schema::hasColumn('b2b_product_details', 'documents')) {
                $table->json('documents')->nullable()->after('images');
            }
        });
    }

    public function down(): void
    {
        Schema::table('b2b_product_details', function (Blueprint $table) {
            $table->dropColumn(['url_slug', 'documents']);
        });
    }
};