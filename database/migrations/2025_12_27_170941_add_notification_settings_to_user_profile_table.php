<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b2b_user_profile', function (Blueprint $table) {
            // Добавляем boolean поля со значением по умолчанию (обычно true — включено)
            $table->boolean('notify_general')->default(true);
            $table->boolean('notify_news')->default(true);
            $table->boolean('notify_orders')->default(true);
            $table->boolean('notify_ticket')->default(true);
            // notify_manager не добавляем, так как в старом коде он disabled (всегда включен)
        });
    }

    public function down(): void
    {
        Schema::table('b2b_user_profile', function (Blueprint $table) {
            $table->dropColumn(['notify_general', 'notify_news', 'notify_orders', 'notify_ticket']);
        });
    }
};