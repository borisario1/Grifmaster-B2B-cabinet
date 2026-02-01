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
        if (Schema::hasTable('command_logs')) {
             Schema::table('command_logs', function (Blueprint $table) {
                $table->integer('progress_current')->default(0)->after('status');
                $table->integer('progress_max')->default(100)->after('progress_current');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('command_logs')) {
            Schema::table('command_logs', function (Blueprint $table) {
                $table->dropColumn(['progress_current', 'progress_max']);
            });
        }
    }
};
