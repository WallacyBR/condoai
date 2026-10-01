<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Block;
use App\Models\Condominium;
use App\Models\ParkingSpot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class ParkingSpotControllerTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    private Condominium $condominium;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominium = Condominium::factory()->create();
        $this->user = User::factory()->create(['condominium_id' => $this->condominium->id]);

        $this->actingAsTenant($this->user, $this->condominium);
    }

    public function test_can_create_parking_spot_without_manual_condominium_id(): void
    {
        $apartment = $this->createApartment(['number' => '101']);

        $this->postJson('/api/parking-spots', [
            'identifier' => 'G1-01',
            'apartment_id' => $apartment->id,
        ])->assertCreated()
            ->assertJsonFragment([
                'identifier' => 'G1-01',
                'condominium_id' => $this->condominium->id,
                'apartment_id' => $apartment->id,
            ]);

        $this->assertDatabaseHas('parking_spots', [
            'identifier' => 'G1-01',
            'condominium_id' => $this->condominium->id,
            'apartment_id' => $apartment->id,
        ]);
    }

    public function test_can_create_parking_spots_in_batch_without_manual_condominium_id(): void
    {
        $apartmentA = $this->createApartment(['number' => '101']);
        $apartmentB = $this->createApartment(['number' => '102']);

        $this->postJson('/api/parking-spots/batch', [
            'spots_per_apartment' => 2,
        ])->assertCreated()
            ->assertJsonCount(4);

        foreach ([$apartmentA, $apartmentB] as $apartment) {
            $this->assertDatabaseHas('parking_spots', [
                'identifier' => "{$apartment->number}-V1",
                'condominium_id' => $this->condominium->id,
                'apartment_id' => $apartment->id,
            ]);

            $this->assertDatabaseHas('parking_spots', [
                'identifier' => "{$apartment->number}-V2",
                'condominium_id' => $this->condominium->id,
                'apartment_id' => $apartment->id,
            ]);
        }
    }

    public function test_batch_creation_skips_existing_spots(): void
    {
        $apartment = $this->createApartment(['number' => '101']);

        ParkingSpot::factory()->create([
            'identifier' => '101-V1',
            'condominium_id' => $this->condominium->id,
            'apartment_id' => $apartment->id,
        ]);

        $this->postJson('/api/parking-spots/batch', [
            'spots_per_apartment' => 1,
        ])->assertCreated()
            ->assertJsonCount(0);

        $this->assertSame(1, ParkingSpot::withoutGlobalScopes()
            ->where('condominium_id', $this->condominium->id)
            ->where('identifier', '101-V1')
            ->count());
    }

    private function createApartment(array $attributes = []): Apartment
    {
        $block = Block::factory()->create(['condominium_id' => $this->condominium->id]);

        return Apartment::factory()->create(array_merge([
            'condominium_id' => $this->condominium->id,
            'block_id' => $block->id,
            'owner_id' => null,
            'tenant_id' => null,
        ], $attributes));
    }
}
