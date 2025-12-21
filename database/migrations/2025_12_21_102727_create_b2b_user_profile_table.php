<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создание таблицы профилей b2b_user_profile точно по дампу.
     */
    public function up(): void
    {
        Schema::create('b2b_user_profile', function (Blueprint $table) {
            $table->id();
            // Связь с основной таблицей пользователей
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('b2b_users')->onDelete('cascade');
            
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('job_title', 255)->nullable();
            $table->string('work_phone', 50)->nullable();
            $table->string('messenger', 100)->nullable();
            $table->timestamps(); // Laravel добавит created_at и updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_user_profile');
    }
};