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
        Schema::create('assemblies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('ordinaria'); // ordinaria, extraordinaria
            $table->string('modality')->default('hibrida'); // presencial, virtual, hibrida
            $table->string('status')->default('agendada'); // agendada, em_andamento, encerrada, cancelada
            $table->dateTime('first_call_at');
            $table->dateTime('second_call_at')->nullable();
            $table->string('meeting_link')->nullable();
            $table->string('location')->nullable();
            $table->longText('minutes')->nullable(); // Ata da assembleia
            $table->decimal('quorum_minimum', 5, 2)->nullable(); // Porcentagem mínima de quórum
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('assembly_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_id')->constrained('assemblies')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(1);
            $table->string('voting_type')->default('sim_nao_abstencao'); // sim_nao_abstencao, multipla_escolha, eleicao
            $table->json('options')->nullable(); // array de opções de voto
            $table->string('status')->default('pendente'); // pendente, aberta, encerrada
            $table->string('weight_type')->default('unitario'); // unitario, fracao_ideal
            $table->timestamps();
        });

        Schema::create('assembly_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_agenda_id')->constrained('assembly_agendas')->onDelete('cascade');
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('chosen_option');
            $table->decimal('weight', 8, 4)->default(1.0000);
            $table->boolean('is_proxy')->default(false);
            $table->string('proxy_document')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            // Garantir que uma unidade vote apenas uma vez por pauta
            $table->unique(['assembly_agenda_id', 'apartment_id'], 'unique_vote_per_unit_per_agenda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assembly_votes');
        Schema::dropIfExists('assembly_agendas');
        Schema::dropIfExists('assemblies');
    }
};
