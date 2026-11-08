<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\ProtectiveMeasure;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Carbon\Carbon;

class ProtectiveMeasureApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user; // Usuário autenticado (admin/portaria)
    private string $token;
    private Condominium $condominium;
    private User $protectedResident; // Morador a ser protegido
    private Visitor $aggressorVisitor; // Visitante agressor

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->condominium = Condominium::factory()->create();

        // Usuário que fará as operações (portaria/admin)
        $this->user = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'password' => bcrypt('secret')
        ]);
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => 'secret',
        ]);
        $this->token = $loginResponse['authorization']['token'];

        // Morador e Visitante para os testes
        $this->protectedResident = User::factory()->create(['condominium_id' => $this->condominium->id]);
        $this->aggressorVisitor = Visitor::factory()->create(['condominium_id' => $this->condominium->id]);
    }

    private function getAuthHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_can_create_protective_measure_without_document(): void
    {
        $data = [
            'protected_resident_id' => $this->protectedResident->id,
            'aggressor_visitor_id' => $this->aggressorVisitor->id,
            'measure_description' => 'No contact allowed.',
            'validity_start_date' => Carbon::today()->toDateString(),
            'is_active' => true,
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/access-control/protective-measures', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'protected_resident_id' => $this->protectedResident->id,
                'aggressor_visitor_id' => $this->aggressorVisitor->id,
                'measure_description' => 'No contact allowed.',
                'condominium_id' => $this->condominium->id
            ]);
        $this->assertDatabaseHas('protective_measures', ['aggressor_visitor_id' => $this->aggressorVisitor->id]);
    }

    public function test_can_create_protective_measure_with_document(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $data = [
            'protected_resident_id' => $this->protectedResident->id,
            'aggressor_visitor_id' => $this->aggressorVisitor->id,
            'measure_description' => 'Stay away order.',
            'validity_start_date' => Carbon::today()->toDateString(),
            'document' => $file,
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/access-control/protective-measures', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['measure_description' => 'Stay away order.'])
            ->assertJsonStructure(['id', 'document_url']);

        $measure = ProtectiveMeasure::orderBy('id', 'desc')->first();
        $this->assertNotNull($measure->document_url);
        Storage::disk('public')->assertExists(str_replace(Storage::url(''), '', $measure->document_url));
    }

    public function test_cannot_create_protective_measure_for_entities_from_different_condominiums(): void
    {
        $otherCondoResident = User::factory()->create(); // Outro condomínio

        $data = [
            'protected_resident_id' => $otherCondoResident->id, // Residente de outro condomínio
            'aggressor_visitor_id' => $this->aggressorVisitor->id, // Visitante do condomínio do teste
            'measure_description' => 'Invalid setup.',
            'validity_start_date' => Carbon::today()->toDateString(),
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/access-control/protective-measures', $data);

        // Controller deve validar que morador e visitante pertencem ao condomínio do usuário autenticado
        $response->assertStatus(422); // Ou 403 dependendo da implementação exata
    }


    public function test_can_list_protective_measures(): void
    {
        ProtectiveMeasure::factory()->count(3)->create([
            'condominium_id' => $this->condominium->id,
            'protected_resident_id' => $this->protectedResident->id,
            'aggressor_visitor_id' => $this->aggressorVisitor->id,
        ]);
        ProtectiveMeasure::factory()->count(2)->create(); // Outro condomínio

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/access-control/protective-measures');

        $response->assertStatus(200)
            ->assertJsonCount(3); // Deve listar apenas as do condomínio do usuário logado
    }

    public function test_can_show_protective_measure(): void
    {
        $measure = ProtectiveMeasure::factory()->create(['condominium_id' => $this->condominium->id]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/access-control/protective-measures/{$measure->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $measure->id]);
    }

    public function test_can_update_protective_measure(): void
    {
        $measure = ProtectiveMeasure::factory()->create(['condominium_id' => $this->condominium->id]);
        $updateData = [
            'measure_description' => 'Updated description.',
            'is_active' => false,
            '_method' => 'PUT'
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson("/api/access-control/protective-measures/{$measure->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['measure_description' => 'Updated description.', 'is_active' => false]);
        $this->assertDatabaseHas('protective_measures', ['id' => $measure->id, 'is_active' => false]);
    }

    public function test_can_remove_document_on_update(): void
    {
        $docPath = UploadedFile::fake()->create('doc_to_remove.pdf')->store('protective_measures_docs', 'public');
        $measure = ProtectiveMeasure::factory()->create([
            'condominium_id' => $this->condominium->id,
            'document_url' => Storage::url($docPath)
        ]);
        Storage::disk('public')->assertExists($docPath);

        $updateData = [
            'document_url' => null, // Chave para remover
            '_method' => 'PUT'
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson("/api/access-control/protective-measures/{$measure->id}", $updateData);

        $response->assertStatus(200)->assertJsonFragment(['document_url' => null]);
        $measure->refresh();
        $this->assertNull($measure->document_url);
        Storage::disk('public')->assertMissing($docPath);
    }


    public function test_can_delete_protective_measure(): void
    {
        $docPath = UploadedFile::fake()->create('doc_to_delete_final.pdf')->store('protective_measures_docs', 'public');
        $measure = ProtectiveMeasure::factory()->create([
            'condominium_id' => $this->condominium->id,
            'document_url' => Storage::url($docPath)
        ]);
        Storage::disk('public')->assertExists($docPath);


        $response = $this->withHeaders($this->getAuthHeaders())
            ->deleteJson("/api/access-control/protective-measures/{$measure->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('protective_measures', ['id' => $measure->id]);
        Storage::disk('public')->assertMissing($docPath);
    }
}
