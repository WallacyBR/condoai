<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\User;
use App\Models\Visitor;
use App\Models\ProtectiveMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtectiveMeasureApiDebugTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Condominium $condominium;
    private User $protectedResident;
    private Visitor $aggressorVisitor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominium = Condominium::factory()->create();
        $this->user = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'password' => bcrypt('secret')
        ]);

        $this->protectedResident = User::factory()->create([
            'condominium_id' => $this->condominium->id,
        ]);

        $this->aggressorVisitor = Visitor::factory()->create([
            'condominium_id' => $this->condominium->id,
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => 'secret',
        ]);
        $this->token = $loginResponse['authorization']['token'];
    }

    private function getAuthHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_can_list_protective_measures(): void
    {
        // Criar algumas medidas protetivas
        ProtectiveMeasure::factory()->count(3)->create([
            'condominium_id' => $this->condominium->id,
            'protected_resident_id' => $this->protectedResident->id,
            'aggressor_visitor_id' => $this->aggressorVisitor->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/access-control/protective-measures');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_returns_empty_array_when_no_measures(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/access-control/protective-measures');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/access-control/protective-measures');

        $response->assertStatus(401);
    }

    public function test_user_must_have_condominium(): void
    {
        // Criar usuário sem condomínio
        $userWithoutCondo = User::factory()->create([
            'condominium_id' => null,
            'password' => bcrypt('secret')
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $userWithoutCondo->email,
            'password' => 'secret',
        ]);
        $token = $loginResponse['authorization']['token'];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/access-control/protective-measures');

        $response->assertStatus(403);
    }
}
