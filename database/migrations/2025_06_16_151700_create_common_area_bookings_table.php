<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('common_area_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('common_area_id')->constrained('common_areas')->onDelete('cascade');
            $table->foreignId('apartment_id')->nullable()->constrained('apartments')->onDelete('cascade');
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_hours');
            $table->string('purpose')->nullable();
            $table->integer('guest_count')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('payment_status')->nullable();
            $table->string('status');
            $table->text('special_requirements')->nullable();
            $table->decimal('cleaning_fee', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->boolean('booking_rules_accepted')->default(false);
            $table->dateTime('confirmation_date')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->enum('booking_type', ['custom', 'morning', 'afternoon', 'evening', 'full_day'])->default('custom');
            $table->decimal('total_price', 10, 2)->nullable(); // Mantido para compatibilidade
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('common_area_bookings');
    }
};
