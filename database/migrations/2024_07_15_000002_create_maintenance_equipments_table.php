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
        Schema::create('maintenance_equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained()->onDelete('cascade');
            $table->foreignId('maintenance_area_id')->constrained('maintenance_areas')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location_details')->nullable();
            $table->string('qr_code_hash')->unique()->nullable(); // Nullable inicialmente, pode ser gerado depois
            $table->enum('qr_code_access_level', ['public', 'internal_users', 'specific_users'])->default('internal_users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_equipments');
    }
};
