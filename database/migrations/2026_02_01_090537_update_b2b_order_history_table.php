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
        if (!Schema::hasTable('b2b_order_history')) {
            Schema::create('b2b_order_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('b2b_orders')->cascadeOnDelete();
                $table->foreignId('status_from_id')->nullable()->constrained('b2b_order_statuses')->nullOnDelete();
                $table->foreignId('status_to_id')->nullable()->constrained('b2b_order_statuses')->cascadeOnDelete();
                $table->foreignId('changed_by_id')->nullable()->constrained('b2b_users')->nullOnDelete();
                $table->text('comment')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('b2b_order_history', function (Blueprint $table) {
                if (!Schema::hasColumn('b2b_order_history', 'status_from_id')) {
                    $table->foreignId('status_from_id')->nullable()->after('order_id')->constrained('b2b_order_statuses')->nullOnDelete();
                }
                if (!Schema::hasColumn('b2b_order_history', 'status_to_id')) {
                    $table->foreignId('status_to_id')->nullable()->after('status_from_id')->constrained('b2b_order_statuses')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('b2b_order_history', 'changed_by_id')) {
                    $table->foreignId('changed_by_id')->nullable()->after('status_to_id')->constrained('b2b_users')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_order_history', function (Blueprint $table) {
            $table->dropForeign(['status_from_id']);
            $table->dropForeign(['status_to_id']);
            $table->dropForeign(['changed_by_id']);
            $table->dropColumn(['status_from_id', 'status_to_id', 'changed_by_id']);
        });
    }
};
