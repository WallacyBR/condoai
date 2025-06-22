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
        Schema::table('common_areas', function (Blueprint $table) {
            $table->integer('max_bookings_per_day')->nullable()->after('requires_approval');
            $table->integer('max_bookings_per_month')->nullable()->after('max_bookings_per_day');
            $table->integer('max_bookings_per_year')->nullable()->after('max_bookings_per_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('common_areas', function (Blueprint $table) {
            $table->dropColumn([
                'max_bookings_per_day',
                'max_bookings_per_month',
                'max_bookings_per_year'
            ]);
        });
    }
};
