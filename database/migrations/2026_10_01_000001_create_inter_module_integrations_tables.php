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
        // 1. Vinculação entre Reserva de Área Comum e Contas a Receber
        Schema::table('common_area_bookings', function (Blueprint $table) {
            $table->foreignId('receivable_id')->nullable()->after('payment_status')
                ->constrained('receivables')->nullOnDelete();
        });

        Schema::table('receivables', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->after('expense_request_id')
                ->constrained('common_area_bookings')->nullOnDelete();
        });

        // 2. Vinculação entre Ocorrências e Ordens de Manutenção
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->foreignId('occurrence_id')->nullable()->after('maintenance_equipment_id')
                ->constrained('occurrences')->nullOnDelete();
        });

        Schema::table('occurrences', function (Blueprint $table) {
            $table->foreignId('converted_to_maintenance_id')->nullable()->after('condominium_id')
                ->constrained('maintenance_schedules')->nullOnDelete();
        });

        // 3. Vinculação entre Registros de Manutenção e Solicitação de Despesa
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->decimal('cost', 12, 2)->default(0)->after('status');
            $table->string('invoice_number')->nullable()->after('cost');
            $table->foreignId('expense_request_id')->nullable()->after('invoice_number')
                ->constrained('expense_requests')->nullOnDelete();
        });

        // 4. Token de Convite Express para Visitantes (Compartilhamento WhatsApp e Portaria Express)
        Schema::table('access_requests', function (Blueprint $table) {
            $table->string('invite_token', 64)->nullable()->unique()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_requests', function (Blueprint $table) {
            $table->dropColumn('invite_token');
        });

        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_request_id');
            $table->dropColumn(['cost', 'invoice_number']);
        });

        Schema::table('occurrences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('converted_to_maintenance_id');
        });

        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('occurrence_id');
        });

        Schema::table('receivables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('booking_id');
        });

        Schema::table('common_area_bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('receivable_id');
        });
    }
};
