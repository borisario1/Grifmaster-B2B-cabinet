<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Таблица обращений
        Schema::create('b2b_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('b2b_users');
            
            // Данные организации (snapshot на момент создания)
            $table->unsignedBigInteger('org_id')->nullable()->index();
            $table->string('org_name')->nullable();
            $table->string('org_inn')->nullable();
            $table->string('org_kpp')->nullable();
            $table->string('org_ogrn')->nullable();
            
            // Контакты заявителя
            $table->string('user_email')->nullable();
            $table->string('user_phone')->nullable();
            
            // Суть обращения
            $table->string('category');
            $table->string('topic');
            $table->string('status')->default('open')->index(); // open, closed, etc.
            $table->string('request_code')->unique(); // Uxxxx-REQxxx
            
            $table->timestamps();
        });

        // Таблица сообщений (чат)
        Schema::create('b2b_request_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('b2b_requests')->cascadeOnDelete();
            
            $table->string('sender_type'); // user, staff, system
            $table->unsignedBigInteger('sender_id')->default(0);
            
            $table->text('message');
            $table->boolean('is_read')->default(false);
            
            // В легаси created_at был timestamp, здесь используем стандартный timestamp
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_request_messages');
        Schema::dropIfExists('b2b_requests');
    }
};
