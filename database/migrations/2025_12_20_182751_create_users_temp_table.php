<?php

/**
 * Название: CreateUsersTempTable
 * Дата-время: 20-12-2025 22:12
 * Описание: Создание временной таблицы для хранения данных регистрации
 * до момента подтверждения email 6-значным кодом.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Добавляем префикс b2b_ к имени таблицы
        Schema::create('b2b_users_temp', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('phone', 50)->nullable();
            $table->string('password_hash');
            $table->string('role', 50)->default('partner');
            $table->string('code', 6);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void {
        Schema::dropIfExists('b2b_users_temp');
    }
};