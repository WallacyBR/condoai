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
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('chart_account_id')->constrained('chart_accounts')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->unsignedSmallInteger('month');
            $table->unsignedSmallInteger('year');
            $table->timestamps();

            $table->unique(['condominium_id', 'chart_account_id', 'month', 'year'], 'budget_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
