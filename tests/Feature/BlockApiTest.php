<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Condominium;
use App\Models\Tower;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockApiTest extends TestCase
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

    public function test_authenticated_user_can_create_block(): void
    {
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $tower = Tower::factory()->create([
            'condominium_id' => $condo->id,
            'active' => true,
        ]);
        $data = Block::factory()->make([
            'condominium_id' => $condo->id,
            'tower_id' => $tower->id,
        ])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/blocks', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_authenticated_user_can_update_block(): void
    {
        $token = $this->authenticate();
        $block = Block::factory()->create([
            'condominium_id' => Condominium::factory()->create()->id,
            'tower_id' => null,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/blocks/'.$block->id, ['name' => 'Alterado']);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Alterado']);
    }

    public function test_authenticated_user_can_delete_block(): void
    {
        $token = $this->authenticate();
        $block = Block::factory()->create([
            'condominium_id' => Condominium::factory()->create()->id,
            'tower_id' => null,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/blocks/'.$block->id);

        $response->assertStatus(204);
    }
}
