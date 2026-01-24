<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Таблица уведомлений
        Schema::create('b2b_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('b2b_users')->cascadeOnDelete();
            $table->string('event_type')->index(); // ticket_created, order_status и т.д.
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('link_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Таблица настроек уведомлений
        Schema::create('b2b_user_notification_prefs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('b2b_users')->cascadeOnDelete();
            
            // Настройки (по умолчанию true - включено)
            $table->boolean('notify_orders')->default(true);
            $table->boolean('notify_ticket')->default(true);
            $table->boolean('notify_news')->default(true);
            $table->boolean('notify_general')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_user_notification_prefs');
        Schema::dropIfExists('b2b_notifications');
    }
};
