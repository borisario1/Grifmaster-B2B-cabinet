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
        Schema::table('b2b_requests', function (Blueprint $table) {
            // Добавляем поля для отслеживания последнего ответа
            $table->timestamp('last_reply_at')->nullable()->after('status');
            $table->string('last_reply_by')->nullable()->after('last_reply_at'); // 'user' или 'admin'
            
            // Добавляем назначение админа
            $table->foreignId('admin_id')
                ->nullable()
                ->after('user_id')
                ->constrained('b2b_users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_requests', function (Blueprint $table) {
            // Удаляем новые поля
            $table->dropColumn(['last_reply_at', 'last_reply_by']);
            
            // Удаляем внешний ключ и колонку admin_id
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });
    }
};
