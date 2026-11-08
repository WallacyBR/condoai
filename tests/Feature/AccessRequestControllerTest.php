<?php

namespace Tests\Feature;

use App\Models\AccessRequest;
use App\Models\Apartment;
use App\Models\Block;
use App\Models\Condominium;
use App\Models\Resident;
use App\Models\Tower;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class AccessRequestControllerTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    public function test_my_requests_returns_empty_data_when_user_has_no_resident(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create([
            'condominium_id' => $condominium->id,
            'email' => 'admin@condo.test',
        ]);

        $this->actingAsTenant($user, $condominium)
            ->getJson('/api/access-control/access-requests/my-requests')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_my_requests_returns_only_authenticated_resident_requests(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create([
            'condominium_id' => $condominium->id,
            'email' => 'morador@condo.test',
        ]);
        $resident = Resident::factory()->create([
            'condominium_id' => $condominium->id,
            'email' => $user->email,
        ]);
        $otherResident = Resident::factory()->create(['condominium_id' => $condominium->id]);
        $apartment = $this->makeApartment($condominium);

        $ownPending = $this->makeAccessRequest($condominium, $resident, $apartment, 'pending');
        $ownAuthorized = $this->makeAccessRequest($condominium, $resident, $apartment, 'authorized');
        $ownCancelled = $this->makeAccessRequest($condominium, $resident, $apartment, 'cancelled');
        $otherResidentRequest = $this->makeAccessRequest($condominium, $otherResident, $apartment, 'pending');

        $response = $this->actingAsTenant($user, $condominium)
            ->getJson('/api/access-control/access-requests/my-requests')
            ->assertOk()
            ->assertJsonStructure(['data']);

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($ownPending->id));
        $this->assertTrue($ids->contains($ownAuthorized->id));
        $this->assertFalse($ids->contains($ownCancelled->id));
        $this->assertFalse($ids->contains($otherResidentRequest->id));
    }

    public function test_my_requests_does_not_leak_requests_from_another_condominium(): void
    {
        $condominium = Condominium::factory()->create();
        $otherCondominium = Condominium::factory()->create();
        $user = User::factory()->create([
            'condominium_id' => $condominium->id,
            'email' => 'morador@condo.test',
        ]);
        $resident = Resident::factory()->create([
            'condominium_id' => $condominium->id,
            'email' => $user->email,
        ]);
        $otherResident = Resident::factory()->create(['condominium_id' => $otherCondominium->id]);
        $apartment = $this->makeApartment($condominium);
        $otherApartment = $this->makeApartment($otherCondominium);

        $sameTenantRequest = $this->makeAccessRequest($condominium, $resident, $apartment, 'pending');
        $otherTenantRequest = $this->makeAccessRequest($otherCondominium, $otherResident, $otherApartment, 'pending');

        $response = $this->actingAsTenant($user, $condominium)
            ->getJson('/api/access-control/access-requests/my-requests')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($sameTenantRequest->id));
        $this->assertFalse($ids->contains($otherTenantRequest->id));
    }

    public function test_access_request_listing_does_not_fail_with_resident_relation(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);
        $resident = Resident::factory()->create(['condominium_id' => $condominium->id]);
        $apartment = $this->makeApartment($condominium);
        $this->makeAccessRequest($condominium, $resident, $apartment, 'pending');

        $this->actingAsTenant($user, $condominium)
            ->getJson('/api/access-control/access-requests')
            ->assertOk()
            ->assertJsonPath('data.0.resident.id', $resident->id);
    }

    public function test_gatehouse_listing_returns_data_envelope(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);
        $resident = Resident::factory()->create(['condominium_id' => $condominium->id]);
        $apartment = $this->makeApartment($condominium);
        $accessRequest = $this->makeAccessRequest($condominium, $resident, $apartment, 'authorized');

        $this->actingAsTenant($user, $condominium)
            ->getJson('/api/access-control/access-requests/gatehouse')
            ->assertOk()
            ->assertJsonPath('data.0.id', $accessRequest->id);
    }

    public function test_store_accepts_unit_destination_payload_and_infers_resident(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);
        $apartment = $this->makeApartment($condominium);
        $resident = Resident::factory()->create([
            'condominium_id' => $condominium->id,
            'apartment_id' => $apartment->id,
        ]);

        $this->actingAsTenant($user, $condominium)
            ->postJson('/api/access-control/access-requests', [
                'visitor_name' => 'Visitante Contrato',
                'visitor_document' => 'DOC-CONTRATO',
                'visitor_phone' => '11999999999',
                'apartment_id' => $apartment->id,
                'visit_date' => now()->toISOString(),
                'gatehouse_observation' => 'Criado pela portaria',
                'vehicle' => [
                    'plate' => 'ABC1234',
                    'model' => 'Sedan',
                    'color' => 'Prata',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('access_request.status', 'pending')
            ->assertJsonPath('access_request.apartment_id', $apartment->id)
            ->assertJsonPath('access_request.resident_id', $resident->id)
            ->assertJsonPath('access_request.visitor_vehicle.plate', 'ABC1234');
    }

    public function test_my_requests_returns_requests_for_residents_unit(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create([
            'condominium_id' => $condominium->id,
            'email' => 'morador-unidade@condo.test',
        ]);
        $apartment = $this->makeApartment($condominium);
        $resident = Resident::factory()->create([
            'condominium_id' => $condominium->id,
            'email' => $user->email,
            'apartment_id' => $apartment->id,
        ]);
        $otherResidentInSameUnit = Resident::factory()->create([
            'condominium_id' => $condominium->id,
            'apartment_id' => $apartment->id,
        ]);

        $unitRequest = $this->makeAccessRequest($condominium, $otherResidentInSameUnit, $apartment, 'pending');

        $response = $this->actingAsTenant($user, $condominium)
            ->getJson('/api/access-control/access-requests/my-requests')
            ->assertOk();

        $this->assertContains($unitRequest->id, collect($response->json('data'))->pluck('id'));
    }

    public function test_resident_response_accepts_gatehouse_waiting_status(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);
        $resident = Resident::factory()->create(['condominium_id' => $condominium->id]);
        $apartment = $this->makeApartment($condominium);
        $accessRequest = $this->makeAccessRequest($condominium, $resident, $apartment, 'pending');

        $this->actingAsTenant($user, $condominium)
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/respond", [
                'response' => 'awaiting_at_gatehouse',
                'resident_observation' => 'Aguardar na portaria.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('access_requests', [
            'id' => $accessRequest->id,
            'status' => 'awaiting_at_gatehouse',
        ]);
    }

    private function makeAccessRequest(
        Condominium $condominium,
        Resident $resident,
        Apartment $apartment,
        string $status
    ): AccessRequest {
        $visitor = Visitor::create([
            'condominium_id' => $condominium->id,
            'name' => 'Visitante '.uniqid(),
            'document' => 'DOC-'.uniqid(),
            'phone' => '11999999999',
        ]);

        return AccessRequest::create([
            'condominium_id' => $condominium->id,
            'visitor_id' => $visitor->id,
            'resident_id' => $resident->id,
            'apartment_id' => $apartment->id,
            'visitor_name' => $visitor->name,
            'visitor_document' => $visitor->document,
            'visitor_phone' => $visitor->phone,
            'visit_date' => now()->addDay(),
            'visit_time' => '14:00',
            'duration_hours' => 2,
            'purpose' => 'Visita',
            'status' => $status,
            'request_timestamp' => now(),
        ]);
    }

    private function makeApartment(Condominium $condominium): Apartment
    {
        $tower = Tower::factory()->create(['condominium_id' => $condominium->id]);
        $block = Block::factory()->create([
            'condominium_id' => $condominium->id,
            'tower_id' => $tower->id,
        ]);

        return Apartment::factory()->create([
            'condominium_id' => $condominium->id,
            'tower_id' => $tower->id,
            'block_id' => $block->id,
            'owner_id' => null,
            'tenant_id' => null,
        ]);
    }
}
