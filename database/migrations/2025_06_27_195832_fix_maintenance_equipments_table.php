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
        Schema::table('maintenance_equipments', function (Blueprint $table) {
            // Verificar se as colunas não existem antes de adicionar
            if (!Schema::hasColumn('maintenance_equipments', 'maintenance_area_id')) {
                $table->foreignId('maintenance_area_id')->constrained('maintenance_areas')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('maintenance_equipments', 'name')) {
                $table->string('name');
            }
            
            if (!Schema::hasColumn('maintenance_equipments', 'description')) {
                $table->text('description')->nullable();
            }
            
            if (!Schema::hasColumn('maintenance_equipments', 'location_details')) {
                $table->string('location_details')->nullable();
            }
            
            if (!Schema::hasColumn('maintenance_equipments', 'qr_code_hash')) {
                $table->string('qr_code_hash')->unique()->nullable();
            }
            
            if (!Schema::hasColumn('maintenance_equipments', 'qr_code_access_level')) {
                $table->enum('qr_code_access_level', ['public', 'internal_users', 'specific_users'])->default('internal_users');
            }
            
            if (!Schema::hasColumn('maintenance_equipments', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_equipments', function (Blueprint $table) {
            $table->dropForeign(['maintenance_area_id']);
            $table->dropColumn([
                'maintenance_area_id',
                'name',
                'description', 
                'location_details',
                'qr_code_hash',
                'qr_code_access_level'
            ]);
        });
    }
};
