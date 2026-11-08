<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleApiTest extends TestCase
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

    public function test_authenticated_user_can_create_module(): void
    {
        $token = $this->authenticate();
        $data = Module::factory()->make()->toArray();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/modules', $data);
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_authenticated_user_can_update_module(): void
    {
        $token = $this->authenticate();
        $module = Module::factory()->create();
        $update = ['name' => 'Atualizado'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/modules/' . $module->id, $update);
        $response->assertStatus(200)
            ->assertJsonFragment($update);
    }

    public function test_authenticated_user_can_delete_module(): void
    {
        $token = $this->authenticate();
        $module = Module::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/modules/' . $module->id);
        $response->assertStatus(204);
    }
}
