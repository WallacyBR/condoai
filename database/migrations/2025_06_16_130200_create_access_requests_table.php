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
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('visitor_id')->constrained('visitors')->onDelete('cascade');
            $table->foreignId('visitor_vehicle_id')->nullable()->constrained('visitor_vehicles')->onDelete('set null');
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade'); // Alterado para residents
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->string('visitor_name');
            $table->string('visitor_document');
            $table->string('visitor_phone')->nullable();
            $table->dateTime('visit_date');
            $table->time('visit_time');
            $table->integer('duration_hours')->nullable();
            $table->string('purpose')->nullable();
            $table->string('status'); // Coluna 'status'
            $table->text('observations')->nullable();
            $table->string('authorized_by')->nullable();
            $table->dateTime('authorized_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->dateTime('entry_time')->nullable();
            $table->dateTime('exit_time')->nullable();
            $table->dateTime('request_timestamp')->nullable(); // Coluna 'request_timestamp'
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
