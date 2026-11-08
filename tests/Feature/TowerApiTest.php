<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Tower;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TowerApiTest extends TestCase
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

    public function test_authenticated_user_can_create_tower(): void
    {
        $token = $this->authenticate();
        $condo = Condominium::factory()->create(['active' => true]);
        $data = Tower::factory()->make(['condominium_id' => $condo->id])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/towers', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_authenticated_user_can_update_tower(): void
    {
        $token = $this->authenticate();
        $tower = Tower::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/towers/' . $tower->id, ['name' => 'Atualizada']);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Atualizada']);
    }

    public function test_authenticated_user_can_delete_tower(): void
    {
        $token = $this->authenticate();
        $tower = Tower::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/towers/' . $tower->id);

        $response->assertStatus(204);
    }
}
