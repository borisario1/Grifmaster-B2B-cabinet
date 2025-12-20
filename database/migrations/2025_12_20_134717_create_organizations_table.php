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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            // Связь с таблицей users. Если пользователя удалят, его организации тоже (onDelete).
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('name');
            $table->string('inn', 12);
            $table->string('kpp', 20)->nullable();
            $table->enum('type', ['ip', 'org'])->default('org');
            $table->string('ogrn', 20)->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_deleted')->default(false); // Заменяем 'deleted' на булево значение
            
            $table->timestamps(); // Это заменит created_at и добавит updated_at
        });
        
        Schema::create('organization_infos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->onDelete('cascade');
                
                $table->json('dadata_raw');
                $table->string('status', 20);
                $table->string('branch_type', 10)->nullable();
                $table->string('opf', 255)->nullable(); 
                $table->string('ogrn', 20)->nullable(); 
                $table->string('kpp', 20)->nullable(); 
                $table->string('name_full')->nullable();
                $table->string('name_short')->nullable(); 
                $table->datetime('registered_at')->nullable();
                $table->text('address')->nullable();
                
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
