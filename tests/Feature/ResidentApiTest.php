<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentApiTest extends TestCase
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

    public function test_authenticated_user_can_create_resident(): void
    {
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $data = Resident::factory()->make(['condominium_id' => $condo->id])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/residents', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_authenticated_user_can_update_resident(): void
    {
        $token = $this->authenticate();
        $resident = Resident::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/residents/' . $resident->id, ['name' => 'Alterado']);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Alterado']);
    }
}
