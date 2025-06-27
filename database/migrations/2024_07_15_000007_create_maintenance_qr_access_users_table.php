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
        Schema::create('maintenance_qr_access_users', function (Blueprint $table) {
            $table->id();
            // Não precisa de condominium_id aqui, pois os dois models relacionados já são escopados.
            // A restrição de acesso será feita verificando se o equipamento e o usuário pertencem ao mesmo condomínio
            // antes de criar o relacionamento.
            $table->foreignId('maintenance_equipment_id')->constrained('maintenance_equipments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps(); // Opcional para tabelas pivot, mas pode ser útil

            // Chave primária composta para garantir unicidade e otimizar queries
            $table->unique(['maintenance_equipment_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_qr_access_users');
    }
};
