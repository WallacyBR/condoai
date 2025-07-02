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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->foreignId('resident_id')->nullable()->constrained('residents')->onDelete('set null');
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->string('recipient_name');
            $table->string('recipient_apartment');
            $table->string('recipient_phone')->nullable();
            $table->string('sender');
            $table->text('description')->nullable();
            $table->string('size')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('tracking_code')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivered_to')->nullable();
            $table->boolean('is_delivered')->default(false);
            $table->text('notes')->nullable();
            $table->string('received_by')->nullable();
            $table->string('photo')->nullable();
            $table->string('code', 8)->unique();
            $table->timestamp('retrieved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
