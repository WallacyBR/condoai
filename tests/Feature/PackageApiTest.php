<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Condominium;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageApiTest extends TestCase
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

    public function test_create_package(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\A3o suporta altera\xC3\A7\xC3\A3o de colunas');
        }
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $apartment = Apartment::factory()->create(['condominium_id' => $condo->id]);

        $data = Package::factory()->make(['apartment_id' => $apartment->id])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/packages', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['apartment_id' => $apartment->id]);
    }

    public function test_withdraw_package(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\A3o suporta altera\xC3\A7\xC3\A3o de colunas');
        }
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $apartment = Apartment::factory()->create(['condominium_id' => $condo->id]);
        $package = Package::factory()->create(['apartment_id' => $apartment->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/packages/'.$package->id.'/withdraw', [
                'code' => $package->code,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['retrieved_at' => $package->fresh()->retrieved_at->toJSON()]);
    }
}
