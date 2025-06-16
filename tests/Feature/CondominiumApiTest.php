<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CondominiumApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): string
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret'),
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        return $login['authorization']['token'];
    }

    public function test_create_requires_authentication(): void
    {
        $data = Condominium::factory()->make()->toArray();

        $response = $this->postJson('/api/condominiums', $data);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_condominium(): void
    {
        $token = $this->authenticate();

        $data = Condominium::factory()->make()->toArray();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/condominiums', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_authenticated_user_can_update_condominium(): void
    {
        $token = $this->authenticate();

        $condo = Condominium::factory()->create();
        $update = ['name' => 'Updated'];

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/condominiums/'.$condo->id, $update);

        $response->assertStatus(200)
            ->assertJsonFragment($update);
    }

    public function test_authenticated_user_can_delete_condominium(): void
    {
        $token = $this->authenticate();

        $condo = Condominium::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/condominiums/'.$condo->id);

        $response->assertStatus(204);

        $this->assertSoftDeleted('condominiums', ['id' => $condo->id]);
    }
}
