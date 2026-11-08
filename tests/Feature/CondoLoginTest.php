<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CondoLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_must_belong_to_current_condominium_to_login(): void
    {
        $condoA = Condominium::factory()->create(['domain' => 'alpha']);
        $condoB = Condominium::factory()->create(['domain' => 'beta']);

        $userA = User::factory()->create([
            'email' => 'usera@example.com',
            'password' => bcrypt('secret'),
            'condominium_id' => $condoA->id,
        ]);

        app()->instance('currentCondominium', $condoB);

        $response = $this->postJson('/api/auth/login', [
            'email' => $userA->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login_when_in_correct_condominium(): void
    {
        $condo = Condominium::factory()->create(['domain' => 'alpha']);
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('secret'),
            'condominium_id' => $condo->id,
        ]);

        app()->instance('currentCondominium', $condo);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('user.condominium_id', $condo->id);
    }
}
