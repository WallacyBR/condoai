<?php

namespace Tests\Feature;

use App\Enums\ResidentClassification;
use App\Models\Apartment;
use App\Models\Assembly;
use App\Models\Block;
use App\Models\Condominium;
use App\Models\Profile;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class AssemblyUserLinkTest extends TestCase
{
    use InteractsWithTenant;
    use RefreshDatabase;

    private Condominium $condominium;

    private Condominium $otherCondominium;

    private User $morador;

    private User $ownerUser;

    private User $unlinkedUser;

    private Apartment $apartment;

    private Apartment $ownerApartment;

    private Assembly $assembly;

    private int $agendaId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominium = Condominium::factory()->create();
        $this->otherCondominium = Condominium::factory()->create();

        $moradorProfile = Profile::factory()->create(['name' => 'Morador']);
        $sindicoProfile = Profile::factory()->create(['name' => 'Síndico']);

        $this->morador = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'profile_id' => $moradorProfile->id,
        ]);

        $this->ownerUser = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'profile_id' => $moradorProfile->id,
        ]);

        $this->unlinkedUser = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'profile_id' => $moradorProfile->id,
        ]);

        $block = Block::factory()->create(['condominium_id' => $this->condominium->id]);

        $this->apartment = Apartment::factory()->create([
            'condominium_id' => $this->condominium->id,
            'block_id' => $block->id,
            'number' => '101',
        ]);

        $this->ownerApartment = Apartment::factory()->create([
            'condominium_id' => $this->condominium->id,
            'block_id' => $block->id,
            'number' => '202',
        ]);

        $linkedResident = Resident::factory()->morador()->create([
            'condominium_id' => $this->condominium->id,
            'user_id' => $this->morador->id,
            'apartment_id' => $this->apartment->id,
        ]);
        $this->apartment->residents()->attach($linkedResident->id, ['type' => 'resident', 'active' => true]);

        $ownerResident = Resident::factory()->proprietario()->create([
            'condominium_id' => $this->condominium->id,
            'user_id' => $this->ownerUser->id,
        ]);
        $this->ownerApartment->update(['owner_id' => $ownerResident->id]);

        $this->assembly = Assembly::create([
            'condominium_id' => $this->condominium->id,
            'created_by' => User::factory()->create([
                'condominium_id' => $this->condominium->id,
                'profile_id' => $sindicoProfile->id,
            ])->id,
            'title' => 'Assembleia Teste',
            'type' => 'ordinaria',
            'modality' => 'virtual',
            'status' => 'em_andamento',
            'first_call_at' => now(),
        ]);

        $agenda = $this->assembly->agendas()->create([
            'title' => 'Pauta única',
            'voting_type' => 'sim_nao_abstencao',
            'options' => ['A favor', 'Contra', 'Abstenção'],
            'status' => 'aberta',
            'order' => 1,
        ]);

        $this->agendaId = $agenda->id;
    }

    public function test_unlinked_user_cannot_vote(): void
    {
        $this->actingAsTenant($this->unlinkedUser, $this->condominium);

        $response = $this->postJson("/api/assemblies/{$this->assembly->id}/agendas/{$this->agendaId}/vote", [
            'apartment_id' => $this->apartment->id,
            'chosen_option' => 'A favor',
        ]);

        $response->assertForbidden();
    }

    public function test_linked_resident_can_vote(): void
    {
        $this->actingAsTenant($this->morador, $this->condominium);

        $response = $this->postJson("/api/assemblies/{$this->assembly->id}/agendas/{$this->agendaId}/vote", [
            'apartment_id' => $this->apartment->id,
            'chosen_option' => 'A favor',
        ]);

        $response->assertCreated()
            ->assertJsonPath('vote.apartment_id', $this->apartment->id);
    }

    public function test_non_resident_owner_can_vote(): void
    {
        $this->actingAsTenant($this->ownerUser, $this->condominium);

        $response = $this->postJson("/api/assemblies/{$this->assembly->id}/agendas/{$this->agendaId}/vote", [
            'apartment_id' => $this->ownerApartment->id,
            'chosen_option' => 'Contra',
        ]);

        $response->assertCreated()
            ->assertJsonPath('vote.apartment_id', $this->ownerApartment->id);
    }

    public function test_cross_tenant_vote_is_denied(): void
    {
        $this->morador->condominiums()->syncWithoutDetaching([$this->otherCondominium->id]);

        $this->actingAsTenant($this->morador, $this->otherCondominium);

        $response = $this->postJson("/api/assemblies/{$this->assembly->id}/agendas/{$this->agendaId}/vote", [
            'apartment_id' => $this->apartment->id,
            'chosen_option' => 'A favor',
        ]);

        $response->assertNotFound();
    }

    public function test_show_returns_user_apartments_via_resident_link(): void
    {
        $this->actingAsTenant($this->morador, $this->condominium);

        $response = $this->getJson("/api/assemblies/{$this->assembly->id}");

        $response->assertOk()
            ->assertJsonFragment(['user_apartment_ids' => [$this->apartment->id]]);
    }
}
