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
        Schema::create('b2b_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['price_list', 'certificate', 'catalog', '3d_model', 'video', 'other'])->default('other');
            $table->string('file_path');
            $table->foreignId('brand_id')->nullable()->constrained('b2b_brands')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_pinned')->default(false);
            
            // Legacy features from files_script.php
            $table->boolean('require_confirmation')->default(false);
            $table->text('confirmation_text')->nullable();
            $table->string('confirm_btn_text')->default('Скачать');
            $table->string('external_link')->nullable();
            
            $table->timestamps();
            
            $table->index(['brand_id', 'type']);
            $table->index('is_pinned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_resources');
    }
};
