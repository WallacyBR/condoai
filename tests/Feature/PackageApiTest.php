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

        $data = Package::factory()->make([
            'apartment_id' => $apartment->id,
            'user_id' => null,
        ])->toArray();
        unset($data['user_id']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/packages', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'apartment_id' => $apartment->id,
                'user_id' => User::first()->id,
            ]);
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
                'signature' => 'assinatura.png',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'retrieved_at' => $package->fresh()->retrieved_at->toJSON(),
                'signature' => 'assinatura.png',
            ]);
    }

    public function test_update_package(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\A3o suporta altera\xC3\A7\xC3\A3o de colunas');
        }
        $token = $this->authenticate();
        $package = Package::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/packages/'.$package->id, [
                'pickup_location' => 'Portaria 2',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['pickup_location' => 'Portaria 2']);
    }

    public function test_delete_package(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\A3o suporta altera\xC3\A7\xC3\A3o de colunas');
        }
        $token = $this->authenticate();
        $package = Package::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/packages/'.$package->id);

        $response->assertStatus(204);
    }

    public function test_index_filters(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\A3o suporta altera\xC3\A7\xC3\A3o de colunas');
        }
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $apartment = Apartment::factory()->create(['condominium_id' => $condo->id]);
        $other = Apartment::factory()->create(['condominium_id' => $condo->id]);

        $pending = Package::factory()->create(['apartment_id' => $apartment->id]);
        $retrieved = Package::factory()->create([
            'apartment_id' => $other->id,
            'retrieved_at' => now(),
        ]);

        $respPending = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/packages?condominium_id='.$condo->id.'&status=pending');

        $respPending->assertStatus(200)
            ->assertJsonFragment(['id' => $pending->id])
            ->assertJsonMissing(['id' => $retrieved->id]);

        $respSearch = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/packages?unit='.$apartment->number);

        $respSearch->assertStatus(200)
            ->assertJsonFragment(['id' => $pending->id])
            ->assertJsonMissing(['id' => $retrieved->id]);
    }
}
