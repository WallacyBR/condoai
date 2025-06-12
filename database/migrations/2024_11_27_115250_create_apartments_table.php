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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('number', 10);
            $table->integer('floor');
            $table->foreignId('block_id')->constrained('blocks')->onDelete('cascade');
            $table->foreignId('owner_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['block_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
