<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Assembly;
use App\Models\AssemblyVote;
use App\Models\Block;
use App\Models\Condominium;
use App\Models\Profile;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class AssemblyTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    private Condominium $condominium;

    private User $sindico;

    private User $morador;

    private Apartment $apartment;

    private Resident $resident;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominium = Condominium::factory()->create();

        $sindicoProfile = Profile::factory()->create(['name' => 'Síndico']);
        $this->sindico = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'profile_id' => $sindicoProfile->id,
        ]);

        $moradorProfile = Profile::factory()->create(['name' => 'Morador']);
        $this->morador = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'profile_id' => $moradorProfile->id,
        ]);

        $block = Block::factory()->create(['condominium_id' => $this->condominium->id]);
        $this->apartment = Apartment::factory()->create([
            'condominium_id' => $this->condominium->id,
            'block_id' => $block->id,
            'number' => '202',
            'floor' => 2,
        ]);

        $this->resident = Resident::create([
            'condominium_id' => $this->condominium->id,
            'user_id' => $this->morador->id,
            'name' => 'Morador Votante',
            'email' => 'votante@teste.com',
            'phone' => '11988888888',
            'apartment_id' => $this->apartment->id,
        ]);

        $this->apartment->update(['owner_id' => $this->resident->id]);
    }

    public function test_can_create_assembly_with_agendas_and_list(): void
    {
        $this->actingAsTenant($this->sindico, $this->condominium);

        $assemblyData = [
            'title' => 'Assembleia Geral Ordinária 2026',
            'description' => 'Aprovação de contas e eleição de síndico',
            'type' => 'ordinaria',
            'modality' => 'virtual',
            'first_call_at' => now()->addDays(10)->setTime(19, 0)->format('Y-m-d H:i:s'),
            'second_call_at' => now()->addDays(10)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            'quorum_minimum' => 50.0,
            'agendas' => [
                [
                    'title' => 'Aprovação das contas do exercício 2025',
                    'description' => 'Prestação de contas e parecer do conselho fiscal',
                    'voting_type' => 'sim_nao_abstencao',
                    'weight_type' => 'unitario',
                ],
                [
                    'title' => 'Reforma da impermeabilização da cobertura',
                    'description' => 'Escolha do orçamento para a reforma emergencial',
                    'voting_type' => 'multipla_escolha',
                    'options' => ['Orçamento Construtora Alfa', 'Orçamento Engenharia Beta', 'Adiar Decisão'],
                    'weight_type' => 'unitario',
                ],
            ],
        ];

        $response = $this->postJson('/api/assemblies', $assemblyData);
        $response->assertStatus(201)
            ->assertJsonPath('title', 'Assembleia Geral Ordinária 2026')
            ->assertJsonCount(2, 'agendas');

        $this->assertDatabaseHas('assemblies', [
            'title' => 'Assembleia Geral Ordinária 2026',
            'status' => 'agendada',
            'condominium_id' => $this->condominium->id,
        ]);

        $listResponse = $this->getJson('/api/assemblies');
        $listResponse->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_start_assembly_and_open_voting(): void
    {
        $this->actingAsTenant($this->sindico, $this->condominium);

        $assembly = Assembly::create([
            'condominium_id' => $this->condominium->id,
            'created_by' => $this->sindico->id,
            'title' => 'Assembleia Extraordinária',
            'type' => 'extraordinaria',
            'modality' => 'hibrida',
            'status' => 'agendada',
            'first_call_at' => now()->addDays(5),
        ]);

        $agenda = $assembly->agendas()->create([
            'title' => 'Instalação de placas solares',
            'voting_type' => 'sim_nao_abstencao',
            'options' => ['A favor', 'Contra', 'Abstenção'],
            'status' => 'pendente',
            'order' => 1,
        ]);

        // Iniciar assembleia
        $startResponse = $this->postJson("/api/assemblies/{$assembly->id}/start");
        $startResponse->assertOk()
            ->assertJsonPath('assembly.status', 'em_andamento');

        // Abrir votação da pauta
        $openResponse = $this->postJson("/api/assemblies/{$assembly->id}/agendas/{$agenda->id}/open");
        $openResponse->assertOk()
            ->assertJsonPath('agenda.status', 'aberta');

        $this->assertEquals('aberta', $agenda->fresh()->status);
    }

    public function test_resident_can_cast_vote_and_results_are_tallied(): void
    {
        $assembly = Assembly::create([
            'condominium_id' => $this->condominium->id,
            'created_by' => $this->sindico->id,
            'title' => 'Assembleia em Andamento',
            'type' => 'ordinaria',
            'modality' => 'virtual',
            'status' => 'em_andamento',
            'first_call_at' => now(),
        ]);

        $agenda = $assembly->agendas()->create([
            'title' => 'Aprovação de novo regulamento interno',
            'voting_type' => 'sim_nao_abstencao',
            'options' => ['A favor', 'Contra', 'Abstenção'],
            'status' => 'aberta',
            'order' => 1,
            'weight_type' => 'unitario',
        ]);

        // Morador autenticado vota
        $this->actingAsTenant($this->morador, $this->condominium);

        $voteData = [
            'apartment_id' => $this->apartment->id,
            'chosen_option' => 'A favor',
        ];

        $voteResponse = $this->postJson("/api/assemblies/{$assembly->id}/agendas/{$agenda->id}/vote", $voteData);
        $voteResponse->assertStatus(201)
            ->assertJsonPath('tally.total_votes', 1)
            ->assertJsonPath('tally.breakdown.A favor.count', 1)
            ->assertJsonPath('tally.breakdown.A favor.percentage', 100);

        $this->assertDatabaseHas('assembly_votes', [
            'assembly_agenda_id' => $agenda->id,
            'apartment_id' => $this->apartment->id,
            'user_id' => $this->morador->id,
            'chosen_option' => 'A favor',
        ]);
    }

    public function test_prevents_duplicate_vote_for_same_unit(): void
    {
        $assembly = Assembly::create([
            'condominium_id' => $this->condominium->id,
            'created_by' => $this->sindico->id,
            'title' => 'Assembleia Voto Único',
            'type' => 'ordinaria',
            'modality' => 'virtual',
            'status' => 'em_andamento',
            'first_call_at' => now(),
        ]);

        $agenda = $assembly->agendas()->create([
            'title' => 'Taxa extra de modernização',
            'voting_type' => 'sim_nao_abstencao',
            'options' => ['A favor', 'Contra', 'Abstenção'],
            'status' => 'aberta',
            'order' => 1,
            'weight_type' => 'unitario',
        ]);

        $this->actingAsTenant($this->morador, $this->condominium);

        AssemblyVote::create([
            'assembly_agenda_id' => $agenda->id,
            'apartment_id' => $this->apartment->id,
            'user_id' => $this->morador->id,
            'chosen_option' => 'A favor',
            'weight' => 1.0,
        ]);

        $duplicateResponse = $this->postJson("/api/assemblies/{$assembly->id}/agendas/{$agenda->id}/vote", [
            'apartment_id' => $this->apartment->id,
            'chosen_option' => 'Contra',
        ]);

        $duplicateResponse->assertStatus(422)
            ->assertJsonFragment(['message' => 'Esta unidade já registrou seu voto nesta pauta.']);
    }

    public function test_closing_assembly_generates_preliminary_minutes(): void
    {
        $this->actingAsTenant($this->sindico, $this->condominium);

        $assembly = Assembly::create([
            'condominium_id' => $this->condominium->id,
            'created_by' => $this->sindico->id,
            'title' => 'Assembleia Final',
            'type' => 'ordinaria',
            'modality' => 'virtual',
            'status' => 'em_andamento',
            'first_call_at' => now(),
        ]);

        $agenda = $assembly->agendas()->create([
            'title' => 'Pauta Única',
            'voting_type' => 'sim_nao_abstencao',
            'options' => ['A favor', 'Contra', 'Abstenção'],
            'status' => 'aberta',
            'order' => 1,
            'weight_type' => 'unitario',
        ]);

        AssemblyVote::create([
            'assembly_agenda_id' => $agenda->id,
            'apartment_id' => $this->apartment->id,
            'user_id' => $this->morador->id,
            'chosen_option' => 'A favor',
            'weight' => 1.0,
        ]);

        $closeResponse = $this->postJson("/api/assemblies/{$assembly->id}/close");
        $closeResponse->assertOk()
            ->assertJsonPath('assembly.status', 'encerrada');

        $assembly->refresh();
        $this->assertEquals('encerrada', $assembly->status);
        $this->assertEquals('encerrada', $agenda->fresh()->status);
        $this->assertNotEmpty($assembly->minutes);
        $this->assertStringContainsString('ATA DA ASSEMBLEIA GERAL ORDINARIA', $assembly->minutes);
        $this->assertStringContainsString('A favor: 1 votos (100%)', $assembly->minutes);
    }
}
