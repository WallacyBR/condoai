<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Apartment;
use App\Models\CommonArea;
use App\Models\Condominium;
use App\Models\Package;
use App\Models\Profile;
use App\Models\Resident;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use InteractsWithTenant;
    use RefreshDatabase;

    protected Condominium $condominiumA;

    protected Condominium $condominiumB;

    protected Profile $profile;

    protected User $userA;

    protected User $userB;

    protected User $sysOpUser;

    protected Resident $residentA;

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

        $this->residentA = Resident::factory()->create(['condominium_id' => $this->condominiumA->id]);
        Resident::factory()->create(['condominium_id' => $this->condominiumB->id]);

        CommonArea::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'name' => 'Salão Alpha',
        ]);
        CommonArea::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'name' => 'Salão Beta',
        ]);

        $apartmentA = Apartment::factory()->create(['condominium_id' => $this->condominiumA->id]);
        $apartmentB = Apartment::factory()->create(['condominium_id' => $this->condominiumB->id]);

        Package::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'apartment_id' => $apartmentA->id,
            'user_id' => $this->userA->id,
        ]);
        Package::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'apartment_id' => $apartmentB->id,
            'user_id' => $this->userB->id,
        ]);

        Visitor::query()->create([
            'condominium_id' => $this->condominiumA->id,
            'name' => 'Visitante Alpha',
            'document' => '11111111111',
        ]);
        Visitor::query()->create([
            'condominium_id' => $this->condominiumB->id,
            'name' => 'Visitante Beta',
            'document' => '22222222222',
        ]);

        Ad::query()->create([
            'user_id' => $this->userA->id,
            'condominium_id' => $this->condominiumA->id,
            'title' => 'Anúncio Alpha',
            'category' => 'Geral',
            'condition' => 'Novo',
        ]);
        Ad::query()->create([
            'user_id' => $this->userB->id,
            'condominium_id' => $this->condominiumB->id,
            'title' => 'Anúncio Beta',
            'category' => 'Geral',
            'condition' => 'Novo',
        ]);
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
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->getJson('/api/residents');

        $response->assertOk();
        $this->assertCount(1, $response->json());

        foreach ($response->json() as $resident) {
            $this->assertSame($this->condominiumA->id, $resident['condominium_id']);
        }
    }

    public function test_resident_create_ignores_cross_tenant_condominium_id_in_body(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->postJson('/api/residents', [
            'name' => 'Morador Cross Tenant',
            'cpf' => '99988877766',
            'email' => 'cross-tenant@example.com',
            'person_type' => 'fisica',
            'billing_address_type' => 'condominium',
            'classification' => 'morador',
            'condominium_id' => $this->condominiumB->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumA->id);

        $this->assertDatabaseHas('residents', [
            'email' => 'cross-tenant@example.com',
            'condominium_id' => $this->condominiumA->id,
        ]);
        $this->assertDatabaseMissing('residents', [
            'email' => 'cross-tenant@example.com',
            'condominium_id' => $this->condominiumB->id,
        ]);
    }

    public function test_resident_update_ignores_cross_tenant_condominium_id_in_body(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->putJson('/api/residents/'.$this->residentA->id, [
            'name' => 'Morador Atualizado',
            'condominium_id' => $this->condominiumB->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('condominium_id', $this->condominiumA->id);

        $this->assertDatabaseHas('residents', [
            'id' => $this->residentA->id,
            'name' => 'Morador Atualizado',
            'condominium_id' => $this->condominiumA->id,
        ]);
    }

    /**
     * @dataProvider tenantScopedListingProvider
     */
    public function test_tenant_scoped_resource_listings_are_isolated(
        string $endpoint,
        string $identifierField,
        mixed $expectedValue
    ): void {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->getJson($endpoint.'?condominium_id='.$this->condominiumB->id);

        $response->assertOk();
        $payload = $response->json();
        $items = isset($payload['data']) ? $payload['data'] : $payload;

        $this->assertCount(1, $items);

        if ($identifierField === 'condominium_id') {
            $this->assertSame($this->condominiumA->id, $items[0][$identifierField]);
        } else {
            $this->assertSame($expectedValue, $items[0][$identifierField]);
        }
    }

    public static function tenantScopedListingProvider(): array
    {
        return [
            'common areas' => ['/api/common-areas', 'name', 'Salão Alpha'],
            'packages' => ['/api/packages', 'condominium_id', null],
            'visitors' => ['/api/access-control/visitors', 'document', '11111111111'],
            'ads' => ['/api/ads', 'title', 'Anúncio Alpha'],
        ];
    }
}
