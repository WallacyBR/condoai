<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Condominium;
use App\Models\Tower;
use App\Models\Block;
use App\Models\Apartment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApartmentApiTest extends TestCase
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

    public function test_create_simple_apartment(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite não suporta alteração de colunas');
        }
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();

        $data = Apartment::factory()->make([
            'condominium_id' => $condo->id,
            'owner_id' => null,
            'tenant_id' => null,
        ])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/apartments', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['number' => $data['number']]);
    }

    public function test_create_apartment_with_tower_and_block(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite não suporta alteração de colunas');
        }
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $tower = Tower::factory()->create(['condominium_id' => $condo->id]);
        $block = Block::factory()->create(['condominium_id' => $condo->id, 'tower_id' => $tower->id]);

        $data = Apartment::factory()->make([
            'condominium_id' => $condo->id,
            'tower_id' => $tower->id,
            'block_id' => $block->id,
            'owner_id' => null,
            'tenant_id' => null,
        ])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/apartments', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['tower_id' => $tower->id, 'block_id' => $block->id]);
    }
}
