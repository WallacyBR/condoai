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
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('apartments', function (Blueprint $table) {
                if (! Schema::hasColumn('apartments', 'condominium_id')) {
                    $table->foreignId('condominium_id')->after('id')->constrained('condominiums')->onDelete('cascade');
                }

                if (! Schema::hasColumn('apartments', 'tower_id')) {
                    $table->foreignId('tower_id')->nullable()->after('condominium_id')->constrained('towers')->onDelete('cascade');
                }
            });

            return;
        }

        Schema::table('apartments', function (Blueprint $table) {
            $table->dropForeign(['block_id']);
            $table->dropUnique(['block_id', 'number']);
            $table->foreignId('condominium_id')->after('id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('tower_id')->nullable()->after('condominium_id')->constrained('towers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('apartments', function (Blueprint $table) {
            $table->dropForeign(['condominium_id']);
            $table->dropForeign(['tower_id']);
            $table->dropColumn(['condominium_id', 'tower_id']);
            $table->unique(['block_id', 'number']);
        });
    }
};
