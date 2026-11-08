<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileApiTest extends TestCase
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

    public function test_create_requires_authentication(): void
    {
        $data = Profile::factory()->make()->toArray();
        $response = $this->postJson('/api/profiles', $data);
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_profile(): void
    {
        $token = $this->authenticate();
        $data = Profile::factory()->make()->toArray();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/profiles', $data);
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $token = $this->authenticate();
        $profile = Profile::factory()->create();
        $update = ['name' => 'Atualizado'];
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/profiles/'.$profile->id, $update);
        $response->assertStatus(200)
            ->assertJsonFragment($update);
    }

    public function test_authenticated_user_can_delete_profile(): void
    {
        $token = $this->authenticate();
        $profile = Profile::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/profiles/'.$profile->id);
        $response->assertStatus(204);
    }
}
