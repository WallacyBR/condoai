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
        Schema::create('maintenance_record_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained()->onDelete('cascade'); // For tenancy
            $table->foreignId('maintenance_record_id')->unique()->constrained('maintenance_records')->onDelete('cascade'); // unique assumes one signature per record
            $table->string('signature_path'); // Path to the signature image file
            $table->timestamp('signed_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_record_signatures');
    }
};
