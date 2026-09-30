<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Profile;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Condominium $condominiumA;

    protected Condominium $condominiumB;

    protected Profile $profile;

    protected User $userA;

    protected User $userB;

    protected User $sysOpUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominiumA = Condominium::factory()->create(['name' => 'Condomínio Alpha']);
        $this->condominiumB = Condominium::factory()->create(['name' => 'Condomínio Beta']);
        $this->profile = Profile::factory()->create(['name' => 'Síndico']);
        $sysOpProfile = Profile::factory()->create(['name' => 'SysOp']);

        $this->userA = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->profile->id,
        ]);
        $this->userA->condominiums()->sync([$this->condominiumA->id]);

        $this->userB = User::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'profile_id' => $this->profile->id,
        ]);
        $this->userB->condominiums()->sync([$this->condominiumB->id]);

        $this->sysOpUser = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $sysOpProfile->id,
        ]);
        $this->sysOpUser->condominiums()->sync([$this->condominiumA->id]);

        Resident::factory()->create(['condominium_id' => $this->condominiumA->id]);
        Resident::factory()->create(['condominium_id' => $this->condominiumB->id]);
    }

    public function test_tenant_protected_route_returns_400_without_header(): void
    {
        $this->actingAs($this->userA, 'api');

        $response = $this->getJson('/api/residents');

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Header X-Condominium-Id é obrigatório.',
            ]);
    }

    public function test_cross_tenant_access_returns_403(): void
    {
        $this->actingAs($this->userA, 'api');

        $response = $this->withHeader('X-Condominium-Id', (string) $this->condominiumB->id)
            ->getJson('/api/residents');

        $response->assertForbidden()
            ->assertJson([
                'message' => 'Acesso negado ao condomínio informado.',
            ]);
    }

    public function test_user_only_sees_own_memberships_on_condominiums_index(): void
    {
        $this->actingAs($this->userA, 'api');

        $response = $this->withHeader('X-Condominium-Id', (string) $this->condominiumA->id)
            ->getJson('/api/condominiums');

        $response->assertOk();

        $ids = collect($response->json())->pluck('id');

        $this->assertTrue($ids->contains($this->condominiumA->id));
        $this->assertFalse($ids->contains($this->condominiumB->id));
    }

    public function test_sysop_can_operate_across_tenants(): void
    {
        $this->actingAs($this->sysOpUser, 'api');

        $response = $this->withHeader('X-Condominium-Id', (string) $this->condominiumB->id)
            ->getJson('/api/residents');

        $response->assertOk();
        $this->assertCount(1, $response->json());
    }

    public function test_sysop_sees_all_condominiums_on_index(): void
    {
        $this->actingAs($this->sysOpUser, 'api');

        $response = $this->withHeader('X-Condominium-Id', (string) $this->condominiumA->id)
            ->getJson('/api/condominiums');

        $response->assertOk();

        $ids = collect($response->json())->pluck('id');

        $this->assertTrue($ids->contains($this->condominiumA->id));
        $this->assertTrue($ids->contains($this->condominiumB->id));
    }

    public function test_auth_me_includes_memberships(): void
    {
        $this->actingAs($this->userA, 'api');

        $response = $this->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonStructure([
                'memberships' => [
                    ['id', 'name'],
                ],
            ]);

        $membershipIds = collect($response->json('memberships'))->pluck('id');

        $this->assertTrue($membershipIds->contains($this->condominiumA->id));
        $this->assertFalse($membershipIds->contains($this->condominiumB->id));
    }

    public function test_tenant_scoped_listing_returns_only_current_tenant_data(): void
    {
        $this->actingAs($this->userA, 'api');

        $response = $this->withHeader('X-Condominium-Id', (string) $this->condominiumA->id)
            ->getJson('/api/residents');

        $response->assertOk();
        $this->assertCount(1, $response->json());

        foreach ($response->json() as $resident) {
            $this->assertSame($this->condominiumA->id, $resident['condominium_id']);
        }
    }
}
