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
            return;
        }

        Schema::table('apartments', function (Blueprint $table) {
            $table->dropUnique(['block_id', 'number']);
            $table->foreignId('condominium_id')->after('id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('tower_id')->nullable()->after('condominium_id')->constrained('towers')->onDelete('cascade');
            $table->dropForeign(['block_id']);
            $table->dropColumn('block_id');
            $table->foreignId('block_id')->nullable()->constrained('blocks')->onDelete('cascade');
            $table->unique(['condominium_id', 'number']);
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
            $table->dropUnique(['condominium_id', 'number']);
            $table->dropForeign(['condominium_id']);
            $table->dropForeign(['tower_id']);
            $table->dropForeign(['block_id']);
            $table->dropColumn(['condominium_id', 'tower_id', 'block_id']);
            $table->foreignId('block_id')->constrained('blocks')->onDelete('cascade');
            $table->unique(['block_id', 'number']);
        });
    }
};
