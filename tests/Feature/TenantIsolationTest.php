<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Apartment;
use App\Models\Bank;
use App\Models\CommonArea;
use App\Models\CommonAreaBooking;
use App\Models\Condominium;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceSchedule;
use App\Models\Notice;
use App\Models\Package;
use App\Models\Profile;
use App\Models\Resident;
use App\Models\Tower;
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

    protected Resident $residentB;

    protected CommonArea $commonAreaA;

    protected CommonArea $commonAreaB;

    protected Apartment $apartmentA;

    protected Apartment $apartmentB;

    protected CommonAreaBooking $bookingA;

    protected CommonAreaBooking $bookingB;

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
        $this->residentB = Resident::factory()->create(['condominium_id' => $this->condominiumB->id]);

        $this->commonAreaA = CommonArea::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'name' => 'Salão Alpha',
        ]);
        $this->commonAreaB = CommonArea::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'name' => 'Salão Beta',
        ]);

        $this->apartmentA = Apartment::factory()->create(['condominium_id' => $this->condominiumA->id]);
        $this->apartmentB = Apartment::factory()->create(['condominium_id' => $this->condominiumB->id]);

        $this->bookingA = CommonAreaBooking::query()->create([
            'common_area_id' => $this->commonAreaA->id,
            'apartment_id' => $this->apartmentA->id,
            'resident_id' => $this->residentA->id,
            'condominium_id' => $this->condominiumA->id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'duration_hours' => 2,
            'status' => 'confirmed',
        ]);
        $this->bookingB = CommonAreaBooking::query()->create([
            'common_area_id' => $this->commonAreaB->id,
            'apartment_id' => $this->apartmentB->id,
            'resident_id' => $this->residentB->id,
            'condominium_id' => $this->condominiumB->id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'duration_hours' => 2,
            'status' => 'confirmed',
        ]);

        Package::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'apartment_id' => $this->apartmentA->id,
            'user_id' => $this->userA->id,
        ]);
        Package::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'apartment_id' => $this->apartmentB->id,
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

        Tower::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'name' => 'Torre Alpha',
        ]);
        Tower::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'name' => 'Torre Beta',
        ]);

        Notice::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'user_id' => $this->userA->id,
            'title' => 'Aviso Alpha',
        ]);
        Notice::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'user_id' => $this->userB->id,
            'title' => 'Aviso Beta',
        ]);

        Bank::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'name' => 'Banco Alpha',
        ]);
        Bank::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'name' => 'Banco Beta',
        ]);

        $equipmentA = MaintenanceEquipment::factory()->create([
            'condominium_id' => $this->condominiumA->id,
        ]);
        $equipmentB = MaintenanceEquipment::factory()->create([
            'condominium_id' => $this->condominiumB->id,
        ]);

        MaintenanceSchedule::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'maintenance_equipment_id' => $equipmentA->id,
            'title' => 'Agenda Alpha',
            'type' => 'daily',
            'is_recurring' => true,
        ]);
        MaintenanceSchedule::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'maintenance_equipment_id' => $equipmentB->id,
            'title' => 'Agenda Beta',
            'type' => 'daily',
            'is_recurring' => true,
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
            'towers' => ['/api/towers', 'name', 'Torre Alpha'],
            'banks' => ['/api/finance/banks', 'name', 'Banco Alpha'],
            'notices' => ['/api/notices', 'title', 'Aviso Alpha'],
            'maintenance schedules' => ['/api/maintenance/schedules', 'title', 'Agenda Alpha'],
        ];
    }

    public function test_common_area_create_ignores_cross_tenant_condominium_id_in_body(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->postJson('/api/common-areas', [
            'name' => 'Área Cross Tenant',
            'capacity' => 30,
            'condominium_id' => $this->condominiumB->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumA->id);

        $this->assertDatabaseHas('common_areas', [
            'name' => 'Área Cross Tenant',
            'condominium_id' => $this->condominiumA->id,
        ]);
    }

    public function test_visitor_create_uses_tenant_context_instead_of_user_home_condominium(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->postJson('/api/access-control/visitors', [
            'name' => 'Visitante Tenant Context',
            'document' => '44444444444',
        ]);

        $response->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumA->id);

        $this->assertDatabaseHas('visitors', [
            'document' => '44444444444',
            'condominium_id' => $this->condominiumA->id,
        ]);
    }

    public function test_ad_create_uses_tenant_context_for_condominium_id(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->postJson('/api/ads', [
            'title' => 'Anúncio Tenant Context',
            'category' => 'Geral',
            'condition' => 'Novo',
        ]);

        $response->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumA->id);

        $this->assertDatabaseHas('ads', [
            'title' => 'Anúncio Tenant Context',
            'condominium_id' => $this->condominiumA->id,
        ]);
    }

    public function test_all_bookings_are_isolated_by_tenant_context(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->getJson('/api/common-area-bookings?condominium_id='.$this->condominiumB->id);

        $response->assertOk();
        $this->assertCount(1, $response->json());
        $this->assertSame($this->commonAreaA->id, $response->json('0.common_area_id'));
    }

    public function test_show_booking_returns_same_tenant_booking(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->getJson('/api/common-area-bookings/'.$this->bookingA->id);

        $response->assertOk()
            ->assertJsonPath('id', $this->bookingA->id)
            ->assertJsonPath('common_area_id', $this->commonAreaA->id);
    }

    public function test_show_booking_returns_404_for_cross_tenant_booking(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        // 404 (não 403) para não vazar existência de reserva de outro condomínio — padrão do controller.
        $response = $this->getJson('/api/common-area-bookings/'.$this->bookingB->id);

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Reserva não encontrada',
            ]);
    }

    public function test_condominium_show_denies_non_member(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->getJson('/api/condominiums/'.$this->condominiumB->id);

        $response->assertForbidden()
            ->assertJson([
                'message' => 'Acesso negado ao condomínio informado.',
            ]);
    }

    public function test_sysop_can_show_any_condominium(): void
    {
        $this->actingAsTenant($this->sysOpUser, $this->condominiumB);

        $response = $this->getJson('/api/condominiums/'.$this->condominiumB->id);

        $response->assertOk()
            ->assertJsonPath('id', $this->condominiumB->id);
    }

    public function test_current_condominium_requires_authentication(): void
    {
        $response = $this->withHeader('X-Condominium-Id', (string) $this->condominiumA->id)
            ->getJson('/api/auth/current-condominium');

        $response->assertUnauthorized();
    }

    public function test_current_condominium_denies_cross_tenant_membership(): void
    {
        $this->actingAs($this->userA, 'api');

        $response = $this->withHeader('X-Condominium-Id', (string) $this->condominiumB->id)
            ->getJson('/api/auth/current-condominium');

        $response->assertForbidden()
            ->assertJson([
                'message' => 'Acesso negado ao condomínio informado.',
            ]);
    }

    public function test_current_condominium_returns_tenant_for_member(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->getJson('/api/auth/current-condominium');

        $response->assertOk()
            ->assertJsonPath('current_condominium.id', $this->condominiumA->id);
    }

    public function test_tower_create_uses_tenant_context_instead_of_body(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->postJson('/api/towers', [
            'name' => 'Torre Cross Tenant',
            'condominium_id' => $this->condominiumB->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumA->id);

        $this->assertDatabaseHas('towers', [
            'name' => 'Torre Cross Tenant',
            'condominium_id' => $this->condominiumA->id,
        ]);
    }

    public function test_notice_create_uses_tenant_context_instead_of_user_home(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->postJson('/api/notices', [
            'title' => 'Aviso Tenant Context',
            'body' => 'Conteúdo do aviso',
            'send_to_all' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumA->id);

        $this->assertDatabaseHas('notices', [
            'title' => 'Aviso Tenant Context',
            'condominium_id' => $this->condominiumA->id,
        ]);
    }

    public function test_bank_create_ignores_cross_tenant_condominium_id_in_body(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->postJson('/api/finance/banks', [
            'name' => 'Banco Cross Tenant',
            'account_number' => '12345-6',
            'condominium_id' => $this->condominiumB->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumA->id);

        $this->assertDatabaseHas('banks', [
            'name' => 'Banco Cross Tenant',
            'condominium_id' => $this->condominiumA->id,
        ]);
    }

    public function test_show_tower_returns_404_for_cross_tenant_resource(): void
    {
        $towerB = Tower::where('condominium_id', $this->condominiumB->id)->first();

        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->getJson('/api/towers/'.$towerB->id);

        $response->assertNotFound();
    }

    public function test_maintenance_schedule_listing_respects_tenant_header(): void
    {
        $this->actingAsTenant($this->userA, $this->condominiumA);

        $response = $this->getJson('/api/maintenance/schedules?condominium_id='.$this->condominiumB->id);

        $response->assertOk();
        $items = $response->json('data') ?? $response->json();
        $this->assertCount(1, $items);
        $this->assertSame('Agenda Alpha', $items[0]['title']);
    }
}
