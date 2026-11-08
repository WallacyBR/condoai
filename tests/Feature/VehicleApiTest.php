<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Condominium;
use App\Models\ParkingSpot;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): string
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);
        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        return $login['authorization']['token'];
    }

    public function test_create_vehicle_with_parking_spot(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\A3o suporta altera\xC3\A7\xC3\A3o de colunas');
        }
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $apartment = Apartment::factory()->create(['condominium_id' => $condo->id]);
        $spot = ParkingSpot::factory()->create([
            'condominium_id' => $condo->id,
            'apartment_id' => $apartment->id,
        ]);
        $data = Vehicle::factory()->make([
            'apartment_id' => $apartment->id,
            'parking_spot_id' => $spot->id,
        ])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/vehicles', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'license_plate' => $data['license_plate'],
                'apartment_id' => $apartment->id,
                'parking_spot_id' => $spot->id,
            ]);
    }
}
