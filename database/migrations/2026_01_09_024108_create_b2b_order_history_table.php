<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b2b_order_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('b2b_orders')->onDelete('cascade');
            $table->string('event_type'); // created, status_change, comment
            $table->text('message');
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->foreignId('created_by')->constrained('b2b_users'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_order_history');
    }
};