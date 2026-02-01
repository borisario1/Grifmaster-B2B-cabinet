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
        Schema::create('b2b_order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Системное название статуса');
            $table->string('label', 100)->comment('Отображаемое название');
            $table->string('color', 7)->default('#6B7280')->comment('HEX цвет для badge');
            $table->integer('sort_order')->default(0)->comment('Порядок сортировки');
            $table->boolean('is_default')->default(false)->comment('Статус по умолчанию');
            $table->boolean('is_final')->default(false)->comment('Финальный статус (закрыт/отменен)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_order_statuses');
    }
};
