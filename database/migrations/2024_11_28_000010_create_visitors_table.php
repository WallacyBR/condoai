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
        Schema::create('visitors', function (Blueprint $table) { // Tabela 'visitors'
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->string('name'); // Coluna 'name'
            $table->string('document')->unique(); // Coluna 'document'
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('visitor_type')->nullable();
            $table->string('company')->nullable();
            $table->string('photo_url')->nullable();
            $table->text('observations')->nullable();
            $table->boolean('is_blocked')->default(false); // Coluna 'is_blocked'
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors'); // Tabela 'visitors'
    }
};
