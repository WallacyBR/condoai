<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
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
        $profile = Profile::factory()->create();
        $data = User::factory()->make(['profile_id' => $profile->id])->getAttributes();
        $response = $this->postJson('/api/users', $data);
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_user(): void
    {
        $token = $this->authenticate();
        $profile = Profile::factory()->create();
        $data = User::factory()->make(['profile_id' => $profile->id])->getAttributes();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users', $data);
        $response->assertStatus(201)
            ->assertJsonFragment(['email' => $data['email']]);
    }

    public function test_authenticated_user_can_update_user(): void
    {
        $token = $this->authenticate();
        $profile = Profile::factory()->create();
        $user = User::factory()->create(['profile_id' => $profile->id]);
        $update = ['name' => 'Atualizado'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/users/' . $user->id, $update);
        $response->assertStatus(200)
            ->assertJsonFragment($update);
    }

    public function test_authenticated_user_can_delete_user(): void
    {
        $token = $this->authenticate();
        $profile = Profile::factory()->create();
        $user = User::factory()->create(['profile_id' => $profile->id]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/users/' . $user->id);
        $response->assertStatus(204);
    }
}
