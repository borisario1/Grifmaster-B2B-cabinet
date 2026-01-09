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
        Schema::table('b2b_orders', function (Blueprint $table) {
            $table->string('org_kpp', 20)->nullable()->after('org_inn');
            $table->string('org_ogrn', 20)->nullable()->after('org_kpp');
            $table->integer('total_items')->after('org_ogrn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_orders', function (Blueprint $table) {
            //
        });
    }
};
