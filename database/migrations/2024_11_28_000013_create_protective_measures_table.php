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
        Schema::create('protective_measures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('protected_resident_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('aggressor_visitor_id')->constrained('visitors')->onDelete('cascade');
            $table->string('process_number')->nullable();
            $table->text('measure_description');
            $table->string('document_url')->nullable();
            $table->date('validity_start_date');
            $table->date('validity_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['protected_resident_id', 'aggressor_visitor_id', 'is_active'], 'idx_protective_measure_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protective_measures');
    }
};
