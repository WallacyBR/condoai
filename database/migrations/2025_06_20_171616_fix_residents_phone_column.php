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
        Schema::table('residents', function (Blueprint $table) {
            // Verificar se a coluna phone existe e cell_phone não existe
            if (Schema::hasColumn('residents', 'phone') && ! Schema::hasColumn('residents', 'cell_phone')) {
                $table->renameColumn('phone', 'cell_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            // Verificar se a coluna cell_phone existe e phone não existe
            if (Schema::hasColumn('residents', 'cell_phone') && ! Schema::hasColumn('residents', 'phone')) {
                $table->renameColumn('cell_phone', 'phone');
            }
        });
    }
};
