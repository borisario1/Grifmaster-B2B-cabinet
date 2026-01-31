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
        Schema::create('b2b_resource_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('b2b_resources')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('b2b_users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('downloaded_at')->useCurrent();
            
            $table->index(['resource_id', 'downloaded_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_resource_stats');
    }
};
