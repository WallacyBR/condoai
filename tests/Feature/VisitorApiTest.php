<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Visitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VisitorApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Condominium $condominium;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public'); // Fake o storage para uploads

        $this->condominium = Condominium::factory()->create();
        $this->user = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'password' => bcrypt('secret')
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => 'secret',
        ]);
        $this->token = $loginResponse['authorization']['token'];
    }

    private function getAuthHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_can_create_visitor_without_photo(): void
    {
        $data = [
            'name' => 'John Doe Visitor',
            'document' => '12345678900',
            'phone' => '11999998888',
            'condominium_id' => $this->condominium->id, // Testando envio explícito, mas o controller deve usar o do user
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/access-control/visitors', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'John Doe Visitor', 'document' => '12345678900']);
        $this->assertDatabaseHas('visitors', ['document' => '12345678900', 'condominium_id' => $this->condominium->id]);
    }

    public function test_can_create_visitor_with_photo(): void
    {
        $file = UploadedFile::fake()->image('visitor.jpg');
        $data = [
            'name' => 'Jane Doe Visitor',
            'document' => '00987654321',
            'photo' => $file,
            'condominium_id' => $this->condominium->id,
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/access-control/visitors', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Jane Doe Visitor'])
            ->assertJsonStructure(['id', 'name', 'document', 'photo_url']);

        $visitor = Visitor::where('document', '00987654321')->first();
        $this->assertNotNull($visitor->photo_url);
        Storage::disk('public')->assertExists(str_replace(Storage::url(''), '', $visitor->photo_url));
    }

    public function test_cannot_create_visitor_with_duplicate_document_in_same_condominium(): void
    {
        Visitor::factory()->create([
            'document' => '1122334455',
            'condominium_id' => $this->condominium->id,
        ]);
        $data = Visitor::factory()->make([
            'document' => '1122334455',
            'condominium_id' => $this->condominium->id,
        ])->toArray();

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/access-control/visitors', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['document']);
    }

    public function test_can_create_visitor_with_duplicate_document_in_different_condominium(): void
    {
        $otherCondominium = Condominium::factory()->create();
        Visitor::factory()->create([
            'document' => '1122334455',
            'condominium_id' => $otherCondominium->id, // Visitante no outro condomínio
        ]);

        // Novo usuário e token para o condomínio atual do teste
        // (o controller usa o condominium_id do usuário autenticado)
        $data = [
            'name' => 'Unique Visitor',
            'document' => '1122334455', // Mesmo documento, mas para o $this->condominium
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/access-control/visitors', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['document' => '1122334455']);
        $this->assertDatabaseHas('visitors', [
            'document' => '1122334455',
            'condominium_id' => $this->condominium->id
        ]);
    }


    public function test_can_list_visitors_of_condominium(): void
    {
        Visitor::factory()->count(3)->create(['condominium_id' => $this->condominium->id]);
        Visitor::factory()->count(2)->create(); // Outro condomínio

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/access-control/visitors?condominium_id=' . $this->condominium->id); // Testando com query param

        $response->assertStatus(200)
            ->assertJsonCount(3);

        // Testando sem query param (deve usar o do usuário autenticado)
        $responseUserCondo = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/access-control/visitors');

        $responseUserCondo->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_show_visitor(): void
    {
        $visitor = Visitor::factory()->create(['condominium_id' => $this->condominium->id]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/access-control/visitors/{$visitor->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $visitor->id]);
    }

    public function test_cannot_show_visitor_from_another_condominium(): void
    {
        $otherCondoVisitor = Visitor::factory()->create(); // Pertence a outro condomínio

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/access-control/visitors/{$otherCondoVisitor->id}?condominium_id={$this->condominium->id}");
        // Controller deve filtrar pelo condominiumId do user ou do query param, se user tiver acesso.
        // Neste caso, o VisitorController no método show está pegando o condominium_id do query param.
        // Se o visitante não pertence àquele condomínio, deve retornar 404.

        $response->assertStatus(404);
    }


    public function test_can_update_visitor(): void
    {
        $visitor = Visitor::factory()->create(['condominium_id' => $this->condominium->id]);
        $file = UploadedFile::fake()->image('new_photo.png');
        $updateData = [
            'name' => 'Updated Name',
            'phone' => '2233334444',
            'photo' => $file,
            '_method' => 'PUT', // Necessário para simular PUT com FormData
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson("/api/access-control/visitors/{$visitor->id}", $updateData); // Usar postJson com _method para FormData

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name', 'phone' => '2233334444']);

        $visitor->refresh();
        $this->assertNotNull($visitor->photo_url);
        Storage::disk('public')->assertExists(str_replace(Storage::url(''), '', $visitor->photo_url));
        $this->assertStringContainsString('new_photo', $visitor->photo_url);
    }

    public function test_can_remove_visitor_photo_on_update(): void
    {
        $path = UploadedFile::fake()->image('visitor_to_delete.jpg')->store('visitor_photos', 'public');
        $visitor = Visitor::factory()->create([
            'condominium_id' => $this->condominium->id,
            'photo_url' => Storage::url($path)
        ]);
        Storage::disk('public')->assertExists($path);

        $updateData = [
            'name' => 'Still Here',
            'photo_url' => null, // Chave para remover a foto
             '_method' => 'PUT',
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson("/api/access-control/visitors/{$visitor->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['photo_url' => null]);

        $visitor->refresh();
        $this->assertNull($visitor->photo_url);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_can_delete_visitor(): void
    {
        $path = UploadedFile::fake()->image('visitor_to_delete_final.jpg')->store('visitor_photos', 'public');
        $visitor = Visitor::factory()->create([
            'condominium_id' => $this->condominium->id,
            'photo_url' => Storage::url($path)
        ]);
        Storage::disk('public')->assertExists($path);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->deleteJson("/api/access-control/visitors/{$visitor->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('visitors', ['id' => $visitor->id]);
        Storage::disk('public')->assertMissing($path); // Verifica se a foto foi removida do storage
    }

    public function test_can_search_visitor_by_document(): void
    {
        $visitor = Visitor::factory()->create([
            'condominium_id' => $this->condominium->id,
            'document' => 'DOC123SEARCH'
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/access-control/visitors/search-by-document?document=DOC123SEARCH");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $visitor->id, 'document' => 'DOC123SEARCH']);
    }

    public function test_search_visitor_by_document_not_found(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/access-control/visitors/search-by-document?document=NONEXISTENTDOC");

        $response->assertStatus(404);
    }
}
