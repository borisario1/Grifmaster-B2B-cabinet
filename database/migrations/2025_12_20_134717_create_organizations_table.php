<?php

/**
 * Название: CreateOrganizationsTable
 * Дата-время: 21-12-2025 00:50
 * Описание: Создание таблиц организаций и расширенной информации с явным указанием связей.
 */

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
        // 1. Таблица организаций
        Schema::create('b2b_organizations', function (Blueprint $table) {
            $table->id();
            
            // Явно указываем таблицу b2b_users, иначе Laravel ищет просто 'users'
            $table->foreignId('user_id')
                  ->constrained('b2b_users') 
                  ->onDelete('cascade');
            
            $table->string('name');
            $table->string('inn', 12);
            $table->string('kpp', 20)->nullable();
            $table->enum('type', ['ip', 'org'])->default('org');
            $table->string('ogrn', 20)->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_deleted')->default(false); 
            
            $table->timestamps();
        });
        
        // 2. Таблица доп. информации (DaData)
        Schema::create('b2b_organization_infos', function (Blueprint $table) {
            $table->id();
            
            // Здесь тоже лучше указать имя таблицы b2b_organizations явно
            $table->foreignId('organization_id')
                  ->constrained('b2b_organizations')
                  ->onDelete('cascade');
            
            $table->json('dadata_raw');
            $table->string('status', 20);
            $table->string('branch_type', 10)->nullable();
            $table->string('opf', 255)->nullable(); 
            $table->string('ogrn', 20)->nullable(); 
            $table->string('kpp', 20)->nullable(); 
            $table->string('name_full')->nullable();
            $table->string('name_short')->nullable(); 
            $table->datetime('registered_at')->nullable();
            $table->text('address')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем в обратном порядке из-за внешних ключей
        Schema::dropIfExists('b2b_organization_infos');
        Schema::dropIfExists('b2b_organizations');
    }
};