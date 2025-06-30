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
        Schema::create('access_requests', function (Blueprint $table) { // Tabela 'access_requests'
            $table->id();
            $table->foreignId('condominium_id')->constrained()->onDelete('cascade');
            $table->foreignId('visitor_id')->constrained('visitors')->onDelete('cascade');
            $table->foreignId('visitor_vehicle_id')->nullable()->constrained('visitor_vehicles')->onDelete('set null');
            $table->foreignId('resident_id')->constrained('users')->onDelete('cascade'); // Coluna 'resident_id'
            $table->dateTime('request_timestamp'); // Coluna 'request_timestamp'
            $table->string('status'); // Coluna 'status'
            $table->text('resident_observation')->nullable(); // Coluna 'resident_observation'
            $table->text('gatehouse_observation')->nullable(); // Coluna 'gatehouse_observation'
            $table->dateTime('resident_response_timestamp')->nullable(); // Coluna 'resident_response_timestamp'
            $table->dateTime('actual_entry_timestamp')->nullable(); // Coluna 'actual_entry_timestamp'
            $table->dateTime('actual_exit_timestamp')->nullable(); // Coluna 'actual_exit_timestamp'
            $table->foreignId('parking_spot_id')->nullable()->constrained('parking_spots')->onDelete('set null'); // Coluna 'parking_spot_id'
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_requests'); // Tabela 'access_requests'
    }
};
