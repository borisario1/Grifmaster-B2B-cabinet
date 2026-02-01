<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('b2b_orders', function (Blueprint $table) {
            // Добавляем новые поля с проверкой существования
            if (!Schema::hasColumn('b2b_orders', 'status_id')) {
                $table->foreignId('status_id')->nullable()->constrained('b2b_order_statuses')->onDelete('restrict');
            }
            if (!Schema::hasColumn('b2b_orders', 'admin_id')) {
                $table->foreignId('admin_id')->nullable()->constrained('b2b_users')->onDelete('set null');
            }
            if (!Schema::hasColumn('b2b_orders', 'closure_comment')) {
                $table->text('closure_comment')->nullable();
            }
            if (!Schema::hasColumn('b2b_orders', 'closed_at')) {
                $table->timestamp('closed_at')->nullable();
            }
            if (!Schema::hasColumn('b2b_orders', 'last_status_change_at')) {
                $table->timestamp('last_status_change_at')->nullable();
            }
        });

        // Мигрируем существующие данные: status 'new' -> status_id
        $newStatusId = DB::table('b2b_order_statuses')->where('name', 'new')->value('id');
        
        if ($newStatusId) {
            // Проверяем, есть ли поле status как source
            if (Schema::hasColumn('b2b_orders', 'status')) {
                DB::table('b2b_orders')
                    ->where('status', 'new')
                    ->update(['status_id' => $newStatusId]);
            }
        }

        // Устанавливаем last_status_change_at = created_at для существующих заказов
        if (Schema::hasColumn('b2b_orders', 'last_status_change_at')) {
            DB::table('b2b_orders')
                ->whereNull('last_status_change_at')
                ->update(['last_status_change_at' => DB::raw('created_at')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_orders', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['status_id', 'admin_id', 'closed_at', 'closure_comment', 'last_status_change_at']);
        });
    }
};
