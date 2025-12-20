<?php

/**
 * Название: CreateUserProfilesTable
 * Дата-время: 20-12-2025 22:10
 * Описание: Создание таблицы профилей пользователей b2b_user_profile.
 * Хранит расширенную информацию: ФИО, телефон, должность, мессенджеры.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('b2b_user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('b2b_users')->onDelete('cascade');
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('full_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('work_phone', 50)->nullable();
            $table->string('messenger', 100)->nullable();
            $table->timestamps(); // Это заменит created_at и updated_at
        });
    }

    public function down(): void {
        Schema::dropIfExists('b2b_user_profiles');
    }
};