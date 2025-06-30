<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_jwt_token(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'user',
                'authorization' => ['token', 'type'],
            ]);
    }

    public function test_protected_route_requires_authentication(): void
    {
        $response = $this->getJson('/api/condominiums');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_protected_route(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret'),
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $token = $login['authorization']['token'];

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/condominiums');

        $response->assertStatus(200);
    }

    public function test_login_returns_user_modules(): void
    {
        $profile = \App\Models\Profile::factory()->create();
        $module = \App\Models\Module::factory()->create();
        $profile->modules()->attach($module->id, [
            'can_view' => true,
            'can_create' => true,
            'can_update' => false,
            'can_delete' => false,
            'scope' => 'own',
        ]);
        $user = User::factory()->create([
            'password' => bcrypt('secret'),
            'profile_id' => $profile->id,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.profile.modules.0.id', $module->id)
            ->assertJsonPath('user.profile.modules.0.pivot.can_view', 1);
    }
}
