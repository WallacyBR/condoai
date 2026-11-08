<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Condominium;
use App\Models\ParkingSpot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingSpotApiTest extends TestCase
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

    public function test_create_parking_spot(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\A3o suporta altera\xC3\A7\xC3\A3o de colunas');
        }
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $apartment = Apartment::factory()->create(['condominium_id' => $condo->id]);
        $data = ParkingSpot::factory()->make([
            'condominium_id' => $condo->id,
            'apartment_id' => $apartment->id,
            'rented_to_apartment_id' => null,
        ])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/parking-spots', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['identifier' => $data['identifier']]);
    }

    public function test_rent_parking_spot(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\A3o suporta altera\xC3\A7\xC3\A3o de colunas');
        }
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $owner = Apartment::factory()->create(['condominium_id' => $condo->id]);
        $renter = Apartment::factory()->create(['condominium_id' => $condo->id]);
        $spot = ParkingSpot::factory()->create([
            'condominium_id' => $condo->id,
            'apartment_id' => $owner->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/parking-spots/'.$spot->id.'/rent', [
                'rented_to_apartment_id' => $renter->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['rented_to_apartment_id' => $renter->id]);
    }
}
