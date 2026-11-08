<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_spots', function (Blueprint $table) {
            $table->id();
            $table->string('identifier', 10);
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->foreignId('rented_to_apartment_id')->nullable()->constrained('apartments')->nullOnDelete();
            $table->timestamps();
            $table->unique(['condominium_id', 'identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_spots');
    }
};
