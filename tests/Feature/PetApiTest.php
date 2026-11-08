<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Condominium;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetApiTest extends TestCase
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

    public function test_create_pet(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\u00e3o suporta altera\u00e7\u00e3o de colunas');
        }
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $apartment = Apartment::factory()->create(['condominium_id' => $condo->id]);

        $data = Pet::factory()->make(['apartment_id' => $apartment->id])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/pets', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name'], 'apartment_id' => $apartment->id]);
    }
}
