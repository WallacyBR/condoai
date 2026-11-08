<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Visitor;
use App\Models\VisitorVehicle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorVehicleApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Condominium $condominium;
    private Visitor $visitor;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->visitor = Visitor::factory()->create(['condominium_id' => $this->condominium->id]);
    }

    private function getAuthHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_can_create_visitor_vehicle(): void
    {
        $data = [
            'plate' => 'ABC-1234',
            'model' => 'Gol',
            'color' => 'Prata',
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson("/api/access-control/visitors/{$this->visitor->id}/vehicles", $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'plate' => 'ABC-1234',
                'model' => 'Gol',
                'visitor_id' => $this->visitor->id,
                'condominium_id' => $this->condominium->id
            ]);
        $this->assertDatabaseHas('visitor_vehicles', ['plate' => 'ABC-1234', 'visitor_id' => $this->visitor->id]);
    }

    public function test_cannot_create_vehicle_for_non_existent_visitor(): void
    {
        $nonExistentVisitorId = 999;
        $data = VisitorVehicle::factory()->make()->toArray();

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson("/api/access-control/visitors/{$nonExistentVisitorId}/vehicles", $data);

        $response->assertStatus(404);
    }

    public function test_cannot_create_visitor_vehicle_with_duplicate_plate_in_same_condominium(): void
    {
        VisitorVehicle::factory()->create([
            'visitor_id' => $this->visitor->id,
            'plate' => 'DUP-0001',
            'condominium_id' => $this->condominium->id,
        ]);
        $data = VisitorVehicle::factory()->make([
            'plate' => 'DUP-0001',
            'condominium_id' => $this->condominium->id,
        ])->toArray();

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson("/api/access-control/visitors/{$this->visitor->id}/vehicles", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plate']);
    }

    public function test_can_create_visitor_vehicle_with_duplicate_plate_in_different_condominium(): void
    {
        $otherCondominium = Condominium::factory()->create();
        $otherVisitor = Visitor::factory()->create(['condominium_id' => $otherCondominium->id]);
        VisitorVehicle::factory()->create([
            'visitor_id' => $otherVisitor->id,
            'plate' => 'DUP-0001',
            'condominium_id' => $otherCondominium->id,
        ]);

        $data = [
            'plate' => 'DUP-0001',
            'model' => 'Fusca',
            'color' => 'Azul',
        ];
         // O controller usa o condominium_id do $this->user (e do $this->visitor)

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson("/api/access-control/visitors/{$this->visitor->id}/vehicles", $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['plate' => 'DUP-0001']);
        $this->assertDatabaseHas('visitor_vehicles', [
            'plate' => 'DUP-0001',
            'condominium_id' => $this->condominium->id,
            'visitor_id' => $this->visitor->id
        ]);
    }


    public function test_can_list_visitor_vehicles(): void
    {
        VisitorVehicle::factory()->count(2)->create([
            'visitor_id' => $this->visitor->id,
            'condominium_id' => $this->condominium->id
        ]);
        // Veículo de outro visitante no mesmo condomínio
        $otherVisitorInSameCondo = Visitor::factory()->create(['condominium_id' => $this->condominium->id]);
        VisitorVehicle::factory()->create([
            'visitor_id' => $otherVisitorInSameCondo->id,
            'condominium_id' => $this->condominium->id
        ]);


        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/access-control/visitors/{$this->visitor->id}/vehicles");

        $response->assertStatus(200)
            ->assertJsonCount(2); // Deve listar apenas os veículos do $this->visitor
    }

    public function test_can_show_visitor_vehicle(): void
    {
        $vehicle = VisitorVehicle::factory()->create([
            'visitor_id' => $this->visitor->id,
            'condominium_id' => $this->condominium->id
        ]);

        // Rota não shallow (completa)
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/access-control/visitors/{$this->visitor->id}/vehicles/{$vehicle->id}");
        $response->assertStatus(200)->assertJsonFragment(['id' => $vehicle->id]);

        // Rota shallow (disponibilizada pelo ->shallow() no apiResource)
        $responseShallow = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/access-control/vehicles/{$vehicle->id}");
        $responseShallow->assertStatus(200)->assertJsonFragment(['id' => $vehicle->id]);
    }

    public function test_cannot_show_vehicle_of_another_visitor(): void
    {
        $anotherVisitor = Visitor::factory()->create(['condominium_id' => $this->condominium->id]);
        $vehicleOfAnotherVisitor = VisitorVehicle::factory()->create([
            'visitor_id' => $anotherVisitor->id,
            'condominium_id' => $this->condominium->id
        ]);

        // Tentando acessar o veículo do "anotherVisitor" através da rota do "$this->visitor"
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/access-control/visitors/{$this->visitor->id}/vehicles/{$vehicleOfAnotherVisitor->id}");

        $response->assertStatus(404); // Esperado 404 pois o veículo não pertence ao $this->visitor
    }


    public function test_can_update_visitor_vehicle(): void
    {
        $vehicle = VisitorVehicle::factory()->create([
            'visitor_id' => $this->visitor->id,
            'condominium_id' => $this->condominium->id
        ]);
        $updateData = ['model' => 'Fiesta', 'color' => 'Preto'];

        // Testando rota shallow
        $response = $this->withHeaders($this->getAuthHeaders())
            ->putJson("/api/access-control/vehicles/{$vehicle->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['model' => 'Fiesta', 'color' => 'Preto']);
        $this->assertDatabaseHas('visitor_vehicles', ['id' => $vehicle->id, 'model' => 'Fiesta']);
    }

    public function test_can_delete_visitor_vehicle(): void
    {
        $vehicle = VisitorVehicle::factory()->create([
            'visitor_id' => $this->visitor->id,
            'condominium_id' => $this->condominium->id
        ]);

        // Testando rota shallow
        $response = $this->withHeaders($this->getAuthHeaders())
            ->deleteJson("/api/access-control/vehicles/{$vehicle->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('visitor_vehicles', ['id' => $vehicle->id]);
    }
}
