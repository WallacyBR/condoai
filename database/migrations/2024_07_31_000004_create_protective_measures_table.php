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
        Schema::create('medidas_protetivas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained()->onDelete('cascade');
            $table->foreignId('morador_protegido_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('visitante_agressor_id')->constrained('visitantes')->onDelete('cascade');
            $table->string('numero_processo')->nullable();
            $table->text('descricao_medida');
            $table->string('documento_url')->nullable();
            $table->date('data_inicio_validade');
            $table->date('data_fim_validade')->nullable();
            $table->boolean('ativa')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['morador_protegido_id', 'visitante_agressor_id', 'ativa'], 'idx_medida_protetiva_ativa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medidas_protetivas');
    }
};
