<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('common_areas', function (Blueprint $table) {
            $table->string('main_photo')->nullable()->after('description');
            $table->json('additional_photos')->nullable()->after('main_photo');
            $table->boolean('active')->default(true)->after('capacity');
            $table->json('availability_schedule')->nullable()->after('active'); // Dias e horários de disponibilidade
            $table->json('unavailability_periods')->nullable()->after('availability_schedule'); // Períodos específicos indisponíveis
            $table->decimal('price_per_hour', 10, 2)->nullable()->after('unavailability_periods');
            $table->text('rules')->nullable()->after('price_per_hour');
            $table->integer('min_booking_hours')->default(1)->after('rules');
            $table->integer('max_booking_hours')->default(8)->after('min_booking_hours');
            $table->boolean('requires_approval')->default(false)->after('max_booking_hours');
            
            // Configurações de períodos padrão
            $table->json('booking_periods')->nullable()->after('requires_approval'); // Períodos padrão (manhã, tarde, noite, dia todo)
            $table->boolean('allow_custom_periods')->default(true)->after('booking_periods'); // Permitir reservas customizadas
            
            // Configurações de tipo de reserva por dia da semana
            $table->json('booking_schedule')->nullable()->after('allow_custom_periods'); // Tipo de reserva por dia (hourly/period)
            
            // Limites de reserva
            $table->integer('max_bookings_per_day')->nullable()->after('booking_schedule'); // Máximo de reservas por dia
            $table->integer('max_bookings_per_month')->nullable()->after('max_bookings_per_day'); // Máximo de reservas por mês
            $table->integer('max_bookings_per_year')->nullable()->after('max_bookings_per_month'); // Máximo de reservas por ano
        });
    }

    public function down(): void
    {
        Schema::table('common_areas', function (Blueprint $table) {
            $table->dropColumn([
                'main_photo',
                'additional_photos',
                'active',
                'availability_schedule',
                'unavailability_periods',
                'price_per_hour',
                'rules',
                'min_booking_hours',
                'max_booking_hours',
                'requires_approval',
                'booking_periods',
                'allow_custom_periods',
                'booking_schedule',
                'max_bookings_per_day',
                'max_bookings_per_month',
                'max_bookings_per_year'
            ]);
        });
    }
}; 