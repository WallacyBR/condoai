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
        Schema::create('visitor_vehicles', function (Blueprint $table) { // Tabela 'visitor_vehicles'
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('visitor_id')->constrained('visitors')->onDelete('cascade'); // Referencia 'visitors'
            $table->string('brand');
            $table->string('model');
            $table->string('color');
            $table->string('license_plate');
            $table->string('plate')->nullable(); // Mantido para compatibilidade
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_vehicles'); // Tabela 'visitor_vehicles'
    }
};
