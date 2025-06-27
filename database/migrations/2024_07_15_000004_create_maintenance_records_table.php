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
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained()->onDelete('cascade');
            $table->foreignId('maintenance_schedule_id')->constrained('maintenance_schedules')->onDelete('cascade');
            $table->foreignId('user_id')->comment('User who performed/is assigned to the maintenance')->constrained('users')->onDelete('cascade');

            $table->dateTime('performed_at')->nullable(); // Date and time when the maintenance was actually done
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'overdue', 'under_review', 'reopened', 'archived'])->default('pending');

            $table->foreignId('reviewed_by_user_id')->nullable()->comment('User who reviewed the maintenance')->constrained('users')->onDelete('set null');
            $table->dateTime('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
