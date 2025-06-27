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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('maintenance_equipment_id')->constrained('maintenance_equipments')->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['daily', 'weekly', 'biweekly', 'monthly', 'semiannual', 'annual', 'specific_days']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('due_time')->nullable();
            $table->boolean('is_recurring')->default(false);

            $table->json('specific_days_of_week')->nullable(); // Para type 'weekly' ou 'specific_days' (ex: [1,3,5] para Seg, Qua, Sex)
            $table->integer('day_of_month')->nullable(); // Para type 'monthly' (ex: 15 para dia 15 do mês)
            $table->integer('month_of_year')->nullable(); // Para type 'semiannual' ou 'annual' (ex: 3 para Março)

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
