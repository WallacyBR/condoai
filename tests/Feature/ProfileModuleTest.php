<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileModuleTest extends TestCase
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

    public function test_assign_modules_to_profile(): void
    {
        $token = $this->authenticate();
        $modules = Module::factory()->count(2)->create();
        $data = Profile::factory()->make()->toArray();
        $data['modules'] = $modules->map(function ($m) {
            return [
                'id' => $m->id,
                'can_view' => true,
                'can_create' => true,
                'can_update' => false,
                'can_delete' => false,
                'scope' => 'own',
            ];
        })->toArray();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/profiles', $data);
        $response->assertStatus(201)
            ->assertJsonCount(2, 'modules');
    }

    public function test_sysop_receives_all_modules(): void
    {
        $token = $this->authenticate();
        Module::factory()->count(3)->create();
        $data = [
            'name' => 'SysOp',
            'description' => 'sysop',
        ];
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/profiles', $data);
        $response->assertStatus(201)
            ->assertJsonCount(3, 'modules');
    }

    public function test_privileges_are_saved_with_module(): void
    {
        $token = $this->authenticate();
        $module = Module::factory()->create();
        $data = Profile::factory()->make()->toArray();
        $data['modules'] = [[
            'id' => $module->id,
            'can_view' => true,
            'can_create' => false,
            'can_update' => true,
            'can_delete' => false,
            'scope' => 'condominium',
        ]];

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/profiles', $data);

        $response->assertStatus(201)
            ->assertJsonPath('modules.0.pivot.can_view', 1)
            ->assertJsonPath('modules.0.pivot.scope', 'condominium');
    }
}
