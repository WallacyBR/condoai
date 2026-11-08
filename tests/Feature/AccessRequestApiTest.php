<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\AccessRequest;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorVehicle;
use App\Models\ProtectiveMeasure;
use App\Models\ParkingSpot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Carbon\Carbon;
use App\Notifications\NewAccessRequestNotification;
use App\Notifications\AccessRequestStatusUpdateNotification;


class AccessRequestApiTest extends TestCase
{
    use RefreshDatabase;

    private User $gatehouseUser; // Usuário da portaria
    private string $gatehouseToken;
    private User $residentUser; // Morador
    private string $residentToken;
    private Condominium $condominium;
    private Visitor $visitor;
    private Visitor $visitorWithProtectiveMeasure;
    private VisitorVehicle $visitorVehicle;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake(); // Fake notifications

        $this->condominium = Condominium::factory()->create();

        // Usuário Portaria
        $this->gatehouseUser = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'password' => bcrypt('secret'),
            // 'profile_id' => Profile::where('name', 'Portaria')->first()->id, // Assumindo que existe um perfil Portaria
        ]);
        $loginGatehouse = $this->postJson('/api/auth/login', [
            'email' => $this->gatehouseUser->email,
            'password' => 'secret',
        ]);
        $this->gatehouseToken = $loginGatehouse['authorization']['token'];

        // Usuário Morador
        $this->residentUser = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'password' => bcrypt('secret'),
            // 'profile_id' => Profile::where('name', 'Morador')->first()->id, // Assumindo que existe um perfil Morador
        ]);
        $loginResident = $this->postJson('/api/auth/login', [
            'email' => $this->residentUser->email,
            'password' => 'secret',
        ]);
        $this->residentToken = $loginResident['authorization']['token'];

        $this->visitor = Visitor::factory()->create(['condominium_id' => $this->condominium->id]);
        $this->visitorVehicle = VisitorVehicle::factory()->create([
            'visitor_id' => $this->visitor->id,
            'condominium_id' => $this->condominium->id
        ]);

        $this->visitorWithProtectiveMeasure = Visitor::factory()->create(['condominium_id' => $this->condominium->id, 'name' => 'Agressor Teste']);
        ProtectiveMeasure::factory()->create([
            'condominium_id' => $this->condominium->id,
            'protected_resident_id' => $this->residentUser->id,
            'aggressor_visitor_id' => $this->visitorWithProtectiveMeasure->id,
            'is_active' => true,
            'validity_start_date' => Carbon::yesterday(),
            'validity_end_date' => Carbon::tomorrow(),
        ]);
    }

    private function getGatehouseAuthHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->gatehouseToken];
    }

    private function getResidentAuthHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->residentToken];
    }

    // Testes de Criação de Solicitação (Portaria)
    public function test_gatehouse_can_create_access_request_for_existing_visitor_and_vehicle(): void
    {
        $data = [
            'visitor_id' => $this->visitor->id,
            'visitor_vehicle_id' => $this->visitorVehicle->id,
            'has_vehicle' => true, // importante para o controller processar o veiculo
            'resident_id' => $this->residentUser->id,
            'gatehouse_observation' => 'Visitante conhecido, liberado para contato.',
        ];

        $response = $this->withHeaders($this->getGatehouseAuthHeaders())
            ->postJson('/api/access-control/access-requests', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'visitor_id' => $this->visitor->id,
                'resident_id' => $this->residentUser->id,
                'status' => 'pending'
            ]);
        $this->assertDatabaseHas('access_requests', ['visitor_id' => $this->visitor->id]);
        Notification::assertSentTo($this->residentUser, NewAccessRequestNotification::class);
    }

    public function test_gatehouse_can_create_access_request_for_new_visitor(): void
    {
        $newVisitorData = Visitor::factory()->make(); // Sem condominium_id aqui, será pego do user
        $data = [
            'visitor_name' => $newVisitorData->name,
            'visitor_document' => $newVisitorData->document,
            'has_vehicle' => false,
            'resident_id' => $this->residentUser->id,
        ];

        $response = $this->withHeaders($this->getGatehouseAuthHeaders())
            ->postJson('/api/access-control/access-requests', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['status' => 'pending']);
        $this->assertDatabaseHas('visitors', ['document' => $newVisitorData->document]);
        $this->assertDatabaseHas('access_requests', ['resident_id' => $this->residentUser->id]);
        Notification::assertSentTo($this->residentUser, NewAccessRequestNotification::class);
    }


    public function test_gatehouse_cannot_create_access_request_if_protective_measure_active(): void
    {
        $data = [
            'visitor_id' => $this->visitorWithProtectiveMeasure->id,
            'resident_id' => $this->residentUser->id, // Residente protegido
            'gatehouse_observation' => 'Tentativa de acesso com medida protetiva.',
        ];

        $response = $this->withHeaders($this->getGatehouseAuthHeaders())
            ->postJson('/api/access-control/access-requests', $data);

        $response->assertStatus(403) // Forbidden
            ->assertJsonFragment(['medida_protetiva_ativa' => true]); // 'protective_measure_active'
        $this->assertDatabaseMissing('access_requests', ['visitor_id' => $this->visitorWithProtectiveMeasure->id]);
        Notification::assertNotSentTo($this->residentUser, NewAccessRequestNotification::class);
    }

    public function test_gatehouse_cannot_create_access_request_for_blocked_visitor(): void
    {
        $blockedVisitor = Visitor::factory()->create([
            'condominium_id' => $this->condominium->id,
            'is_blocked' => true
        ]);
        $data = [
            'visitor_id' => $blockedVisitor->id,
            'resident_id' => $this->residentUser->id,
        ];

        $response = $this->withHeaders($this->getGatehouseAuthHeaders())
            ->postJson('/api/access-control/access-requests', $data);

        $response->assertStatus(403)
            ->assertJsonFragment(['visitor_blocked' => true]);
    }


    // Testes de Resposta do Morador
    public function test_resident_can_respond_to_access_request(): void
    {
        $accessRequest = AccessRequest::factory()->create([
            'condominium_id' => $this->condominium->id,
            'visitor_id' => $this->visitor->id,
            'resident_id' => $this->residentUser->id,
            'status' => 'pending',
        ]);

        $responseData = [
            'response' => 'authorized',
            'resident_observation' => 'Pode subir.',
        ];

        $response = $this->withHeaders($this->getResidentAuthHeaders())
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/respond", $responseData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'authorized',
                'resident_observation' => 'Pode subir.'
            ]);
        $this->assertDatabaseHas('access_requests', ['id' => $accessRequest->id, 'status' => 'authorized']);
        // TODO: Testar notificação para portaria quando implementada
    }

    public function test_resident_cannot_respond_to_non_pending_request(): void
    {
        $accessRequest = AccessRequest::factory()->create([
            'condominium_id' => $this->condominium->id,
            'resident_id' => $this->residentUser->id,
            'status' => 'authorized', // Já autorizado
        ]);
        $responseData = ['response' => 'unauthorized'];

        $response = $this->withHeaders($this->getResidentAuthHeaders())
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/respond", $responseData);

        $response->assertStatus(404); // Ou 403/400 dependendo da lógica exata do controller
    }


    // Testes de Registro de Entrada/Saída (Portaria)
    public function test_gatehouse_can_record_entry(): void
    {
        $accessRequest = AccessRequest::factory()->create([
            'condominium_id' => $this->condominium->id,
            'visitor_id' => $this->visitor->id,
            'resident_id' => $this->residentUser->id,
            'status' => 'authorized',
        ]);
        $parkingSpot = ParkingSpot::factory()->create(['condominium_id' => $this->condominium->id]);

        $entryData = [
            'parking_spot_id' => $parkingSpot->id,
            'gatehouse_entry_observation' => 'Entrada registrada.'
        ];

        $response = $this->withHeaders($this->getGatehouseAuthHeaders())
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/record-entry", $entryData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'entry_recorded',
                'parking_spot_id' => $parkingSpot->id
            ]);
        $this->assertDatabaseHas('access_requests', ['id' => $accessRequest->id, 'status' => 'entry_recorded']);
        $this->assertNotNull(AccessRequest::find($accessRequest->id)->actual_entry_timestamp);
        Notification::assertSentTo($this->residentUser, AccessRequestStatusUpdateNotification::class, function ($notification) use ($accessRequest) {
            return $notification->accessRequest->id === $accessRequest->id && $notification->type === 'entry';
        });
    }

    public function test_gatehouse_can_record_exit(): void
    {
        $accessRequest = AccessRequest::factory()->create([
            'condominium_id' => $this->condominium->id,
            'visitor_id' => $this->visitor->id,
            'resident_id' => $this->residentUser->id,
            'status' => 'entry_recorded',
            'actual_entry_timestamp' => Carbon::now(),
        ]);
         $exitData = ['gatehouse_exit_observation' => 'Saída registrada.'];

        $response = $this->withHeaders($this->getGatehouseAuthHeaders())
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/record-exit", $exitData);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'exit_recorded']);
        $this->assertDatabaseHas('access_requests', ['id' => $accessRequest->id, 'status' => 'exit_recorded']);
        $this->assertNotNull(AccessRequest::find($accessRequest->id)->actual_exit_timestamp);
        Notification::assertSentTo($this->residentUser, AccessRequestStatusUpdateNotification::class, function ($notification) use ($accessRequest) {
            return $notification->accessRequest->id === $accessRequest->id && $notification->type === 'exit';
        });
    }

    // Testes de Cancelamento
    public function test_resident_can_cancel_pending_request(): void
    {
        $accessRequest = AccessRequest::factory()->create([
            'condominium_id' => $this->condominium->id,
            'resident_id' => $this->residentUser->id,
            'status' => 'pending',
        ]);
        $cancelData = ['cancellation_observation' => 'Não estarei em casa.'];

        $response = $this->withHeaders($this->getResidentAuthHeaders())
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/cancel", $cancelData);

        $response->assertStatus(200)->assertJsonFragment(['status' => 'cancelled']);
        $this->assertDatabaseHas('access_requests', ['id' => $accessRequest->id, 'status' => 'cancelled']);
    }

    public function test_gatehouse_can_cancel_authorized_request(): void
    {
        $accessRequest = AccessRequest::factory()->create([
            'condominium_id' => $this->condominium->id,
            'resident_id' => $this->residentUser->id,
            'status' => 'authorized',
        ]);
         $cancelData = ['cancellation_observation' => 'Problema identificado.'];


        $response = $this->withHeaders($this->getGatehouseAuthHeaders())
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/cancel", $cancelData);

        $response->assertStatus(200)->assertJsonFragment(['status' => 'cancelled']);
    }

    public function test_cannot_cancel_entry_recorded_request(): void
    {
        $accessRequest = AccessRequest::factory()->create([
            'condominium_id' => $this->condominium->id,
            'status' => 'entry_recorded',
        ]);

        $response = $this->withHeaders($this->getGatehouseAuthHeaders()) // ou resident, dependendo da lógica
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/cancel");

        $response->assertStatus(403); // Ou 400
    }


    // Testes de Listagem
    public function test_gatehouse_can_list_access_requests(): void
    {
        AccessRequest::factory()->count(3)->create(['condominium_id' => $this->condominium->id]);
        AccessRequest::factory()->count(2)->create(); // Outro condomínio

        $response = $this->withHeaders($this->getGatehouseAuthHeaders())
            ->getJson('/api/access-control/access-requests/gatehouse');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data'); // Verifica 3 itens na paginacao
    }

    public function test_resident_can_list_their_pending_requests(): void
    {
        AccessRequest::factory()->count(2)->create([
            'condominium_id' => $this->condominium->id,
            'resident_id' => $this->residentUser->id,
            'status' => 'pending'
        ]);
        AccessRequest::factory()->create([ // Requisição para outro morador
            'condominium_id' => $this->condominium->id,
            'status' => 'pending'
        ]);
         AccessRequest::factory()->create([ // Requisição autorizada para o mesmo morador
            'condominium_id' => $this->condominium->id,
            'resident_id' => $this->residentUser->id,
            'status' => 'authorized'
        ]);


        $response = $this->withHeaders($this->getResidentAuthHeaders())
            ->getJson('/api/access-control/access-requests/my-requests?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_show_access_request_details(): void
    {
        $accessRequest = AccessRequest::factory()->create([
            'condominium_id' => $this->condominium->id,
            'resident_id' => $this->residentUser->id,
            'visitor_id' => $this->visitor->id,
        ]);

        // Testando com usuário da portaria
        $responseGatehouse = $this->withHeaders($this->getGatehouseAuthHeaders())
            ->getJson("/api/access-control/access-requests/{$accessRequest->id}");
        $responseGatehouse->assertStatus(200)->assertJsonFragment(['id' => $accessRequest->id]);

        // Testando com usuário morador (dono da solicitação)
        $responseResident = $this->withHeaders($this->getResidentAuthHeaders())
            ->getJson("/api/access-control/access-requests/{$accessRequest->id}");
        $responseResident->assertStatus(200)->assertJsonFragment(['id' => $accessRequest->id]);
    }
}
