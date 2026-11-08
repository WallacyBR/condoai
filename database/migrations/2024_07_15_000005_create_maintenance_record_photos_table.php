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
        Schema::create('maintenance_record_photos', function (Blueprint $table) {
            $table->id();
            // condominium_id is not strictly necessary here if we always access photos through MaintenanceRecord,
            // but can be useful for direct queries or if a photo could somehow be orphaned.
            // For strong tenancy, it's safer to include it.
            $table->foreignId('condominium_id')->constrained()->onDelete('cascade');
            $table->foreignId('maintenance_record_id')->constrained('maintenance_records')->onDelete('cascade');
            $table->string('file_path');
            $table->timestamp('uploaded_at')->useCurrent(); // Set default to current time
            $table->timestamps(); // Includes created_at and updated_at
            // No softDeletes here, photo deletion should be hard or handled by record soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_record_photos');
    }
};
