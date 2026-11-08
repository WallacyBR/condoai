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
        Schema::create('ad_interactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ad_id')->constrained('ads')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained()->onDelete('set null');
            $table->string('interaction_type'); // Enum: View, Contact_Click, Report, Mark_Sold, Mark_Unsold
            $table->timestamp('interaction_timestamp')->useCurrent();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_interactions');
    }
};