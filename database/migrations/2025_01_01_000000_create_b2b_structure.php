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
        // ==========================================
        // 1. ПОЛЬЗОВАТЕЛИ И АВТОРИЗАЦИЯ
        // ==========================================

        // Таблица пользователей
        Schema::create('b2b_users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('partner'); 
            $table->unsignedBigInteger('selected_org_id')->nullable();
            $table->string('status')->default('active');
            
            // Логирование входа (добавили сразу, чтобы не делать alter table)
            $table->timestamp('last_login')->nullable();
            $table->timestamp('previous_login')->nullable();

            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        // Временная таблица регистрации (до подтверждения кода)
        Schema::create('b2b_users_temp', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('phone', 50)->nullable();
            $table->string('password_hash');
            $table->string('role', 50)->default('partner');
            $table->string('code', 6);
            $table->timestamp('created_at')->useCurrent();
        });

        // Таблица сброса паролей (Laravel default)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Сессии (Laravel default)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // ==========================================
        // 2. ПРОФИЛЬ ПОЛЬЗОВАТЕЛЯ
        // ==========================================

        Schema::create('b2b_user_profile', function (Blueprint $table) {
            $table->id();
            // Связь с основной таблицей пользователей
            $table->foreignId('user_id')
                  ->constrained('b2b_users')
                  ->onDelete('cascade');
            
            // Личные данные
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->date('birth_date')->nullable();
            
            // Рабочие данные
            $table->string('full_name', 255)->nullable();
            $table->string('job_title', 255)->nullable();
            $table->string('work_phone', 50)->nullable();
            $table->string('messenger', 100)->nullable();

            // Настройки уведомлений (объединили сюда из отдельной миграции)
            $table->boolean('notify_general')->default(true);
            $table->boolean('notify_news')->default(true);
            $table->boolean('notify_orders')->default(true);
            $table->boolean('notify_ticket')->default(true);
            
            $table->softDeletes();
            $table->timestamps();
        });

        // ==========================================
        // 3. ОРГАНИЗАЦИИ
        // ==========================================

        Schema::create('b2b_organizations', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')
                  ->constrained('b2b_users') 
                  ->onDelete('cascade');
            
            $table->string('name');
            $table->string('inn', 12);
            $table->string('kpp', 20)->nullable();
            $table->enum('type', ['ip', 'org'])->default('org');
            $table->string('ogrn', 20)->nullable();
            $table->string('address')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
        
        // Доп. информация (DaData)
        Schema::create('b2b_organization_infos', function (Blueprint $table) {
            $table->id();
            
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
            
            $table->softDeletes();
            $table->timestamps();
        });

        // ==========================================
        // 4. КОРЗИНА
        // ==========================================

        // ==========================================
        // 5. СИСТЕМНЫЕ ТАБЛИЦЫ (JOBS, CACHE)
        // ==========================================

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::table('b2b_users', function (Blueprint $table) {
            $table->foreign('selected_org_id')
                ->references('id')
                ->on('b2b_organizations')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем в обратном порядке (сначала зависимые, потом главные)
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        
        //Schema::dropIfExists('b2b_cart_items');
        
        Schema::dropIfExists('b2b_organization_infos');
        Schema::dropIfExists('b2b_organizations');
        
        Schema::dropIfExists('b2b_user_profile');
        
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('b2b_users_temp');
        Schema::dropIfExists('b2b_users');
    }
};