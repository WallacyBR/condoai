<?php

namespace Tests\Feature\Notifications;

use App\Events\Access\AccessRequestEntryWasRecorded;
use App\Events\Access\AccessRequestExitWasRecorded;
use App\Events\Access\AccessRequestWasCreated;
use App\Events\Access\AccessRequestWasResponded;
use App\Events\CommonAreas\CommonAreaBookingWasApproved;
use App\Events\CommonAreas\CommonAreaBookingWasCancelled;
use App\Events\CommonAreas\CommonAreaBookingWasCreated;
use App\Events\Maintenance\MaintenanceRecordWasCompleted;
use App\Events\Maintenance\MaintenanceRecordWasReopened;
use App\Events\Maintenance\MaintenanceScheduleWasAssigned;
use App\Models\AccessRequest;
use App\Models\Apartment;
use App\Models\Block;
use App\Models\CommonArea;
use App\Models\CommonAreaBooking;
use App\Models\Condominium;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\Profile;
use App\Models\Resident;
use App\Models\User;
use App\Http\Controllers\API\AccessRequestController;
use App\Models\Visitor;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\AssignsModulePermissions;
use Tests\Concerns\InteractsWithTenant;
use Tests\Concerns\SeedsDefaultPermissionMatrix;
use Tests\TestCase;

class E4NotificationEmittersTest extends TestCase
{
    use AssignsModulePermissions;
    use InteractsWithTenant;
    use RefreshDatabase;
    use SeedsDefaultPermissionMatrix;

    protected Condominium $condominiumA;

    protected Condominium $condominiumB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedDefaultPermissionMatrix();

        $this->condominiumA = Condominium::factory()->create(['name' => 'Condomínio Alpha']);
        $this->condominiumB = Condominium::factory()->create(['name' => 'Condomínio Beta']);
    }

    public function test_access_store_notifies_apartment_users_not_actor_or_cross_condo(): void
    {
        $accessStaffProfile = $this->profileWithModule('Controle de Acesso', true);
        $actor = $this->createMember($this->condominiumA, $accessStaffProfile);
        $tenantUser = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $otherUnitUser = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $condoBUser = $this->createMember($this->condominiumB, $this->defaultProfile('Morador'));

        $targetApartment = $this->createApartmentWithTenant($this->condominiumA, $tenantUser, '101');
        $this->createApartmentWithTenant($this->condominiumA, $otherUnitUser, '202');

        $resident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'apartment_id' => $targetApartment->id,
        ]);

        $this->actingAsTenant($actor, $this->condominiumA)
            ->postJson('/api/access-control/access-requests', [
                'visitor_name' => 'Visitante Teste',
                'visitor_document' => 'DOC-E4-001',
                'apartment_id' => $targetApartment->id,
                'resident_id' => $resident->id,
                'visit_date' => now()->addDay()->toDateString(),
            ])
            ->assertCreated();

        $this->assertSame(1, $tenantUser->fresh()->notifications()->count());
        $this->assertSame(0, $actor->fresh()->notifications()->count());
        $this->assertSame(0, $otherUnitUser->fresh()->notifications()->count());
        $this->assertSame(0, $condoBUser->fresh()->notifications()->count());

        $notification = $tenantUser->notifications()->first();
        $this->assertSame('access', $notification->data['category']);
        $this->assertSame('Nova solicitação de acesso', $notification->data['title']);
        $this->assertStringStartsWith('access_request.created:', $notification->data['dedupe_key']);
    }

    public function test_access_respond_authorized_notifies_access_staff_not_actor(): void
    {
        $accessStaffProfile = $this->profileWithModule('Controle de Acesso', true);
        $staff = $this->createMember($this->condominiumA, $accessStaffProfile);
        $residentUser = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));

        $apartment = $this->createApartmentWithTenant($this->condominiumA, $residentUser, '101');
        $resident = Resident::withoutGlobalScopes()->findOrFail($apartment->tenant_id);
        $accessRequest = $this->makeAccessRequest($this->condominiumA, $resident, $apartment, 'pending');

        $this->actingAsTenant($residentUser, $this->condominiumA)
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/respond", [
                'response' => 'authorized',
                'resident_observation' => '',
            ])
            ->assertOk();

        $this->assertSame(1, $staff->fresh()->notifications()->count());
        $this->assertSame(0, $residentUser->fresh()->notifications()->count());
        $this->assertSame('Acesso autorizado', $staff->notifications()->first()->data['title']);
    }

    public function test_access_respond_unauthorized_notifies_access_staff(): void
    {
        $accessStaffProfile = $this->profileWithModule('Controle de Acesso', true);
        $staff = $this->createMember($this->condominiumA, $accessStaffProfile);
        $residentUser = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));

        $apartment = $this->createApartmentWithTenant($this->condominiumA, $residentUser, '102');
        $resident = Resident::withoutGlobalScopes()->findOrFail($apartment->tenant_id);
        $accessRequest = $this->makeAccessRequest($this->condominiumA, $resident, $apartment, 'pending');

        $this->actingAsTenant($residentUser, $this->condominiumA)
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/respond", [
                'response' => 'unauthorized',
                'resident_observation' => '',
            ])
            ->assertOk();

        $this->assertSame(1, $staff->fresh()->notifications()->count());
        $this->assertSame('Acesso negado', $staff->notifications()->first()->data['title']);
    }

    public function test_access_respond_other_statuses_create_no_notifications(): void
    {
        $accessStaffProfile = $this->profileWithModule('Controle de Acesso', true);
        $staff = $this->createMember($this->condominiumA, $accessStaffProfile);
        $residentUser = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));

        $apartment = $this->createApartmentWithTenant($this->condominiumA, $residentUser, '103');
        $resident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'apartment_id' => $apartment->id,
        ]);
        $accessRequest = $this->makeAccessRequest($this->condominiumA, $resident, $apartment, 'pending');

        $this->actingAsTenant($residentUser, $this->condominiumA)
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/respond", [
                'response' => 'awaiting_at_gatehouse',
                'resident_observation' => '',
            ])
            ->assertOk();

        $this->assertSame(0, $staff->fresh()->notifications()->count());
    }

    public function test_access_entry_notifies_apartment_and_staff_excluding_actor(): void
    {
        $accessStaffProfile = $this->profileWithModule('Controle de Acesso', true);
        $actor = $this->createMember($this->condominiumA, $accessStaffProfile);
        $otherStaff = $this->createMember($this->condominiumA, $accessStaffProfile);
        $tenantUser = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $condoBStaff = $this->createMember($this->condominiumB, $accessStaffProfile);

        $apartment = $this->createApartmentWithTenant($this->condominiumA, $tenantUser, '104');
        $resident = Resident::factory()->create(['condominium_id' => $this->condominiumA->id, 'apartment_id' => $apartment->id]);
        $accessRequest = $this->makeAccessRequest($this->condominiumA, $resident, $apartment, 'authorized');

        $this->actingAsTenant($actor, $this->condominiumA)
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/record-entry")
            ->assertOk();

        $this->assertSame(1, $tenantUser->fresh()->notifications()->count());
        $this->assertSame(1, $otherStaff->fresh()->notifications()->count());
        $this->assertSame(0, $actor->fresh()->notifications()->count());
        $this->assertSame(0, $condoBStaff->fresh()->notifications()->count());
    }

    public function test_access_exit_notifies_apartment_and_staff(): void
    {
        $accessStaffProfile = $this->profileWithModule('Controle de Acesso', true);
        $actor = $this->createMember($this->condominiumA, $accessStaffProfile);
        $otherStaff = $this->createMember($this->condominiumA, $accessStaffProfile);
        $tenantUser = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));

        $apartment = $this->createApartmentWithTenant($this->condominiumA, $tenantUser, '105');
        $resident = Resident::factory()->create(['condominium_id' => $this->condominiumA->id, 'apartment_id' => $apartment->id]);
        $accessRequest = $this->makeAccessRequest($this->condominiumA, $resident, $apartment, 'entry_recorded');

        $this->actingAsTenant($actor, $this->condominiumA)
            ->postJson("/api/access-control/access-requests/{$accessRequest->id}/record-exit")
            ->assertOk();

        $this->assertSame(1, $tenantUser->fresh()->notifications()->count());
        $this->assertSame(1, $otherStaff->fresh()->notifications()->count());
        $this->assertSame(0, $actor->fresh()->notifications()->count());
    }

    public function test_access_generic_update_creates_no_notifications(): void
    {
        Event::fake([
            AccessRequestWasCreated::class,
            AccessRequestWasResponded::class,
            AccessRequestEntryWasRecorded::class,
            AccessRequestExitWasRecorded::class,
        ]);

        $user = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $apartment = $this->createApartmentWithTenant($this->condominiumA, $user, '106');
        $resident = Resident::factory()->create(['condominium_id' => $this->condominiumA->id, 'apartment_id' => $apartment->id]);
        $accessRequest = $this->makeAccessRequest($this->condominiumA, $resident, $apartment, 'pending');

        $this->actingAs($user, 'api');
        TenantContext::set($this->condominiumA);

        try {
            $request = Request::create(
                "/api/access-control/access-requests/{$accessRequest->id}",
                'PUT',
                ['purpose' => 'Atualização genérica'],
            );
            $request->attributes->set('condominium', $this->condominiumA);

            $response = app(AccessRequestController::class)->update($request, $accessRequest);
            $this->assertSame(200, $response->getStatusCode());
        } finally {
            TenantContext::clear();
        }

        $this->assertSame(0, $user->fresh()->notifications()->count());
        Event::assertNotDispatched(AccessRequestWasCreated::class);
        Event::assertNotDispatched(AccessRequestWasResponded::class);
        Event::assertNotDispatched(AccessRequestEntryWasRecorded::class);
        Event::assertNotDispatched(AccessRequestExitWasRecorded::class);
    }

    public function test_booking_pending_notifies_solicitante_and_booking_staff(): void
    {
        $bookingStaffProfile = $this->profileWithModule('Áreas Comuns', true);
        $staff = $this->createMember($this->condominiumA, $bookingStaffProfile);
        $otherStaff = $this->createMember($this->condominiumA, $bookingStaffProfile);
        $solicitante = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $apartment = $this->createApartmentWithOwner($this->condominiumA, $solicitante, '201');
        $resident = Resident::withoutGlobalScopes()->findOrFail($apartment->owner_id);

        $commonArea = $this->makeCommonArea($this->condominiumA, requiresApproval: true);

        $this->actingAsTenant($staff, $this->condominiumA)
            ->postJson("/api/common-areas/{$commonArea->id}/bookings", [
                'apartment_id' => $apartment->id,
                'start_time' => now()->addDays(2)->setTime(10, 0)->toDateTimeString(),
                'end_time' => now()->addDays(2)->setTime(12, 0)->toDateTimeString(),
                'booking_type' => 'custom',
                'resident_id' => $resident->id,
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'pending');

        $this->assertSame(1, $solicitante->fresh()->notifications()->count());
        $this->assertSame(0, $staff->fresh()->notifications()->count());
        $this->assertSame(1, $otherStaff->fresh()->notifications()->count());
    }

    public function test_booking_confirmed_auto_notifies_only_solicitante_not_self(): void
    {
        $solicitante = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $bookingStaffProfile = $this->profileWithModule('Áreas Comuns', true);
        $staff = $this->createMember($this->condominiumA, $bookingStaffProfile);

        $apartment = $this->createApartmentWithOwner($this->condominiumA, $solicitante, '202');
        $resident = Resident::withoutGlobalScopes()->findOrFail($apartment->owner_id);

        $commonArea = $this->makeCommonArea($this->condominiumA, requiresApproval: false);

        $this->actingAsTenant($solicitante, $this->condominiumA)
            ->postJson("/api/common-areas/{$commonArea->id}/bookings", [
                'apartment_id' => $apartment->id,
                'start_time' => now()->addDays(3)->setTime(14, 0)->toDateTimeString(),
                'end_time' => now()->addDays(3)->setTime(16, 0)->toDateTimeString(),
                'booking_type' => 'custom',
                'resident_id' => $resident->id,
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'confirmed');

        $this->assertSame(0, $solicitante->fresh()->notifications()->count());
        $this->assertSame(0, $staff->fresh()->notifications()->count());
    }

    public function test_booking_confirmed_auto_notifies_solicitante_when_actor_is_staff(): void
    {
        $bookingStaffProfile = $this->profileWithModule('Áreas Comuns', true);
        $staff = $this->createMember($this->condominiumA, $bookingStaffProfile);
        $solicitante = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));

        $apartment = $this->createApartmentWithOwner($this->condominiumA, $solicitante, '203');
        $resident = Resident::withoutGlobalScopes()->findOrFail($apartment->owner_id);

        $commonArea = $this->makeCommonArea($this->condominiumA, requiresApproval: false);

        $this->actingAsTenant($staff, $this->condominiumA)
            ->postJson("/api/common-areas/{$commonArea->id}/bookings", [
                'apartment_id' => $apartment->id,
                'start_time' => now()->addDays(4)->setTime(9, 0)->toDateTimeString(),
                'end_time' => now()->addDays(4)->setTime(11, 0)->toDateTimeString(),
                'booking_type' => 'custom',
                'resident_id' => $resident->id,
            ])
            ->assertCreated();

        $this->assertSame(1, $solicitante->fresh()->notifications()->count());
        $this->assertSame(0, $staff->fresh()->notifications()->count());
    }

    public function test_booking_approve_notifies_solicitante(): void
    {
        $bookingStaffProfile = $this->profileWithModule('Áreas Comuns', true);
        $staff = $this->createMember($this->condominiumA, $bookingStaffProfile);
        $solicitante = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));

        $apartment = $this->createApartmentWithOwner($this->condominiumA, $solicitante, '204');
        $resident = Resident::withoutGlobalScopes()->findOrFail($apartment->owner_id);
        $commonArea = $this->makeCommonArea($this->condominiumA, requiresApproval: true);
        $booking = $this->makeBooking($commonArea, $apartment, $resident, CommonAreaBooking::STATUS_PENDING, 5);

        $this->actingAsTenant($staff, $this->condominiumA)
            ->patchJson("/api/common-areas/{$commonArea->id}/bookings/{$booking->id}/approve")
            ->assertOk();

        $this->assertSame(1, $solicitante->fresh()->notifications()->count());
        $this->assertSame('Reserva aprovada', $solicitante->notifications()->first()->data['title']);
    }

    public function test_booking_reject_notifies_solicitante_when_staff_rejects(): void
    {
        $bookingStaffProfile = $this->profileWithModule('Áreas Comuns', true);
        $staff = $this->createMember($this->condominiumA, $bookingStaffProfile);
        $solicitante = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));

        $apartment = $this->createApartmentWithOwner($this->condominiumA, $solicitante, '205');
        $resident = Resident::withoutGlobalScopes()->findOrFail($apartment->owner_id);
        $commonArea = $this->makeCommonArea($this->condominiumA, requiresApproval: true);
        $booking = $this->makeBooking($commonArea, $apartment, $resident, CommonAreaBooking::STATUS_PENDING, 6);

        $this->actingAsTenant($staff, $this->condominiumA)
            ->patchJson("/api/common-areas/{$commonArea->id}/bookings/{$booking->id}/reject", [
                'reason' => 'Indisponível',
            ])
            ->assertOk();

        $this->assertSame(1, $solicitante->fresh()->notifications()->count());
        $this->assertSame(0, $staff->fresh()->notifications()->count());
    }

    public function test_booking_cancel_notifies_staff_when_solicitante_cancels(): void
    {
        $bookingStaffProfile = $this->profileWithModule('Áreas Comuns', true);
        $staff = $this->createMember($this->condominiumA, $bookingStaffProfile);
        $solicitante = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));

        $apartment = $this->createApartmentWithOwner($this->condominiumA, $solicitante, '206');
        $resident = Resident::withoutGlobalScopes()->findOrFail($apartment->owner_id);
        $commonArea = $this->makeCommonArea($this->condominiumA, requiresApproval: false);
        $booking = $this->makeBooking($commonArea, $apartment, $resident, CommonAreaBooking::STATUS_CONFIRMED, 7);

        $this->actingAsTenant($solicitante, $this->condominiumA)
            ->patchJson("/api/common-areas/{$commonArea->id}/bookings/{$booking->id}/cancel")
            ->assertOk();

        $this->assertSame(1, $staff->fresh()->notifications()->count());
        $this->assertSame(0, $solicitante->fresh()->notifications()->count());
    }

    public function test_booking_without_resident_user_id_still_notifies_staff_on_pending(): void
    {
        $bookingStaffProfile = $this->profileWithModule('Áreas Comuns', true);
        $staff = $this->createMember($this->condominiumA, $bookingStaffProfile);
        $otherStaff = $this->createMember($this->condominiumA, $bookingStaffProfile);

        $block = Block::factory()->create(['condominium_id' => $this->condominiumA->id]);
        $apartment = Apartment::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'block_id' => $block->id,
        ]);
        $resident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'user_id' => null,
            'apartment_id' => $apartment->id,
        ]);
        $apartment->update(['owner_id' => $resident->id]);
        $commonArea = $this->makeCommonArea($this->condominiumA, requiresApproval: true);

        $this->actingAsTenant($staff, $this->condominiumA)
            ->postJson("/api/common-areas/{$commonArea->id}/bookings", [
                'apartment_id' => $apartment->id,
                'start_time' => now()->addDays(8)->setTime(10, 0)->toDateTimeString(),
                'end_time' => now()->addDays(8)->setTime(12, 0)->toDateTimeString(),
                'booking_type' => 'custom',
                'resident_id' => $resident->id,
            ])
            ->assertCreated();

        $this->assertSame(0, $staff->fresh()->notifications()->count());
        $this->assertSame(1, $otherStaff->fresh()->notifications()->count());
    }

    public function test_schedule_assign_notifies_new_assignees_and_staff(): void
    {
        $maintenanceStaffProfile = $this->profileWithModule('Manutenção', true);
        $actor = $this->createMember($this->condominiumA, $maintenanceStaffProfile);
        $newAssignee = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $otherStaff = $this->createMember($this->condominiumA, $maintenanceStaffProfile);
        $equipment = MaintenanceEquipment::factory()->create(['condominium_id' => $this->condominiumA->id]);

        $this->actingAsTenant($actor, $this->condominiumA)
            ->postJson('/api/maintenance/schedules', [
                'title' => 'Revisão mensal',
                'maintenance_equipment_id' => $equipment->id,
                'type' => 'monthly',
                'start_date' => now()->toDateString(),
                'day_of_month' => (int) now()->format('j'),
                'responsible_user_ids' => [$newAssignee->id],
            ])
            ->assertCreated();

        $this->assertSame(1, $newAssignee->fresh()->notifications()->count());
        $this->assertSame(1, $otherStaff->fresh()->notifications()->count());
        $this->assertSame(0, $actor->fresh()->notifications()->count());
    }

    public function test_schedule_update_without_assignee_diff_creates_no_notifications(): void
    {
        $maintenanceStaffProfile = $this->profileWithModule('Manutenção', true);
        $actor = $this->createMember($this->condominiumA, $maintenanceStaffProfile);
        $assignee = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $equipment = MaintenanceEquipment::factory()->create(['condominium_id' => $this->condominiumA->id]);

        $schedule = MaintenanceSchedule::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'maintenance_equipment_id' => $equipment->id,
            'type' => 'monthly',
            'day_of_month' => 15,
        ]);
        $schedule->responsibleUsers()->sync([$assignee->id]);

        $this->actingAsTenant($actor, $this->condominiumA)
            ->putJson("/api/maintenance/schedules/{$schedule->id}", [
                'title' => 'Título atualizado',
                'responsible_user_ids' => [$assignee->id],
            ])
            ->assertOk();

        $this->assertSame(0, $assignee->fresh()->notifications()->count());
        $this->assertSame(0, $actor->fresh()->notifications()->count());
    }

    public function test_maintenance_record_complete_notifies_assignees_and_staff_not_outsider(): void
    {
        $maintenanceStaffProfile = $this->profileWithModule('Manutenção', true);
        $actor = $this->createMember($this->condominiumA, $maintenanceStaffProfile);
        $assignee = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $otherStaff = $this->createMember($this->condominiumA, $maintenanceStaffProfile);
        $outsiderProfile = Profile::factory()->create(['name' => 'Sem Manutenção']);
        $this->assignModulePermissions($outsiderProfile, 'Ocorrências', ['can_view' => true, 'can_create' => false, 'can_update' => false, 'can_delete' => false]);
        $outsider = $this->createMember($this->condominiumA, $outsiderProfile);

        $equipment = MaintenanceEquipment::factory()->create(['condominium_id' => $this->condominiumA->id]);
        $schedule = MaintenanceSchedule::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'maintenance_equipment_id' => $equipment->id,
            'type' => 'monthly',
            'day_of_month' => 10,
        ]);
        $schedule->responsibleUsers()->sync([$assignee->id]);

        $this->actingAsTenant($actor, $this->condominiumA)
            ->postJson('/api/maintenance/records', [
                'maintenance_schedule_id' => $schedule->id,
                'performed_at' => now()->format('Y-m-d H:i:s'),
                'status' => 'completed',
            ])
            ->assertCreated();

        $this->assertSame(1, $assignee->fresh()->notifications()->count());
        $this->assertSame(1, $otherStaff->fresh()->notifications()->count());
        $this->assertSame(0, $actor->fresh()->notifications()->count());
        $this->assertSame(0, $outsider->fresh()->notifications()->count());
    }

    public function test_maintenance_record_reopen_notifies_assignees_and_staff(): void
    {
        $maintenanceStaffProfile = $this->profileWithModule('Manutenção', true);
        $actor = $this->createMember($this->condominiumA, $maintenanceStaffProfile);
        $assignee = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $otherStaff = $this->createMember($this->condominiumA, $maintenanceStaffProfile);

        $equipment = MaintenanceEquipment::factory()->create(['condominium_id' => $this->condominiumA->id]);
        $schedule = MaintenanceSchedule::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'maintenance_equipment_id' => $equipment->id,
        ]);
        $schedule->responsibleUsers()->sync([$assignee->id]);

        $record = MaintenanceRecord::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'maintenance_schedule_id' => $schedule->id,
            'user_id' => $assignee->id,
            'status' => 'completed',
            'performed_at' => now(),
        ]);

        $this->actingAsTenant($actor, $this->condominiumA)
            ->postJson("/api/maintenance/records/{$record->id}/update-status", [
                'status' => 'reopened',
            ])
            ->assertOk();

        $this->assertSame(1, $assignee->fresh()->notifications()->count());
        $this->assertSame(1, $otherStaff->fresh()->notifications()->count());
        $this->assertSame('Manutenção reaberta', $assignee->notifications()->first()->data['title']);
    }

    public function test_dedupe_prevents_second_notification_for_same_access_request(): void
    {
        $tenantUser = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $apartment = $this->createApartmentWithTenant($this->condominiumA, $tenantUser, '301');
        $resident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'apartment_id' => $apartment->id,
        ]);
        $accessRequest = $this->makeAccessRequest($this->condominiumA, $resident, $apartment, 'pending');

        event(new AccessRequestWasCreated(
            condominiumId: $this->condominiumA->id,
            actorId: null,
            accessRequestId: $accessRequest->id,
        ));
        event(new AccessRequestWasCreated(
            condominiumId: $this->condominiumA->id,
            actorId: null,
            accessRequestId: $accessRequest->id,
        ));

        $this->assertSame(1, $tenantUser->fresh()->notifications()->count());
    }

    public function test_access_store_dispatches_event(): void
    {
        Event::fake([AccessRequestWasCreated::class]);

        $staffProfile = $this->profileWithModule('Controle de Acesso', true);
        $actor = $this->createMember($this->condominiumA, $staffProfile);
        $apartment = Apartment::factory()->create(['condominium_id' => $this->condominiumA->id]);
        $resident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'apartment_id' => $apartment->id,
        ]);

        $this->actingAsTenant($actor, $this->condominiumA)
            ->postJson('/api/access-control/access-requests', [
                'visitor_name' => 'Evento Teste',
                'visitor_document' => 'DOC-EVENT',
                'apartment_id' => $apartment->id,
                'resident_id' => $resident->id,
                'visit_date' => now()->addDay()->toDateString(),
            ])
            ->assertCreated();

        Event::assertDispatched(AccessRequestWasCreated::class);
    }

    public function test_booking_store_dispatches_created_event(): void
    {
        Event::fake([CommonAreaBookingWasCreated::class]);

        $staffProfile = $this->profileWithModule('Áreas Comuns', true);
        $staff = $this->createMember($this->condominiumA, $staffProfile);
        $commonArea = $this->makeCommonArea($this->condominiumA, requiresApproval: true);
        $block = Block::factory()->create(['condominium_id' => $this->condominiumA->id]);
        $apartment = Apartment::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'block_id' => $block->id,
        ]);
        $resident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'apartment_id' => $apartment->id,
        ]);
        $apartment->update(['owner_id' => $resident->id]);

        $this->actingAsTenant($staff, $this->condominiumA)
            ->postJson("/api/common-areas/{$commonArea->id}/bookings", [
                'apartment_id' => $apartment->id,
                'start_time' => now()->addDays(9)->setTime(10, 0)->toDateTimeString(),
                'end_time' => now()->addDays(9)->setTime(12, 0)->toDateTimeString(),
                'booking_type' => 'custom',
                'resident_id' => $resident->id,
            ])
            ->assertCreated();

        Event::assertDispatched(CommonAreaBookingWasCreated::class);
    }

    public function test_schedule_store_dispatches_assigned_event(): void
    {
        Event::fake([MaintenanceScheduleWasAssigned::class]);

        $staffProfile = $this->profileWithModule('Manutenção', true);
        $actor = $this->createMember($this->condominiumA, $staffProfile);
        $assignee = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $equipment = MaintenanceEquipment::factory()->create(['condominium_id' => $this->condominiumA->id]);

        $this->actingAsTenant($actor, $this->condominiumA)
            ->postJson('/api/maintenance/schedules', [
                'title' => 'Evento assign',
                'maintenance_equipment_id' => $equipment->id,
                'type' => 'monthly',
                'start_date' => now()->toDateString(),
                'day_of_month' => (int) now()->format('j'),
                'responsible_user_ids' => [$assignee->id],
            ])
            ->assertCreated();

        Event::assertDispatched(MaintenanceScheduleWasAssigned::class);
    }

    public function test_record_update_status_dispatches_completed_and_reopened_events(): void
    {
        $staffProfile = $this->profileWithModule('Manutenção', true);
        $actor = $this->createMember($this->condominiumA, $staffProfile);
        $equipment = MaintenanceEquipment::factory()->create(['condominium_id' => $this->condominiumA->id]);
        $schedule = MaintenanceSchedule::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'maintenance_equipment_id' => $equipment->id,
        ]);
        $record = MaintenanceRecord::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'maintenance_schedule_id' => $schedule->id,
            'status' => 'pending',
            'performed_at' => now(),
        ]);

        Event::fake([MaintenanceRecordWasCompleted::class, MaintenanceRecordWasReopened::class]);

        $this->actingAsTenant($actor, $this->condominiumA)
            ->postJson("/api/maintenance/records/{$record->id}/update-status", ['status' => 'completed'])
            ->assertOk();

        Event::assertDispatched(MaintenanceRecordWasCompleted::class);

        Event::fake([MaintenanceRecordWasReopened::class]);

        $this->actingAsTenant($actor, $this->condominiumA)
            ->postJson("/api/maintenance/records/{$record->id}/update-status", ['status' => 'reopened'])
            ->assertOk();

        Event::assertDispatched(MaintenanceRecordWasReopened::class);
    }

    public function test_booking_approve_and_cancel_dispatch_events(): void
    {
        Event::fake([CommonAreaBookingWasApproved::class, CommonAreaBookingWasCancelled::class]);

        $staffProfile = $this->profileWithModule('Áreas Comuns', true);
        $staff = $this->createMember($this->condominiumA, $staffProfile);
        $commonArea = $this->makeCommonArea($this->condominiumA, requiresApproval: true);
        $block = Block::factory()->create(['condominium_id' => $this->condominiumA->id]);
        $apartment = Apartment::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'block_id' => $block->id,
        ]);
        $resident = Resident::factory()->create(['condominium_id' => $this->condominiumA->id]);
        $booking = $this->makeBooking($commonArea, $apartment, $resident, CommonAreaBooking::STATUS_PENDING, 10);

        $this->actingAsTenant($staff, $this->condominiumA)
            ->patchJson("/api/common-areas/{$commonArea->id}/bookings/{$booking->id}/approve")
            ->assertOk();

        Event::assertDispatched(CommonAreaBookingWasApproved::class);

        $booking->update(['status' => CommonAreaBooking::STATUS_CONFIRMED, 'start_time' => now()->addDays(11)->setTime(10, 0), 'end_time' => now()->addDays(11)->setTime(12, 0)]);

        $this->actingAsTenant($staff, $this->condominiumA)
            ->patchJson("/api/common-areas/{$commonArea->id}/bookings/{$booking->id}/cancel")
            ->assertOk();

        Event::assertDispatched(CommonAreaBookingWasCancelled::class);
    }

    private function profileWithModule(string $moduleName, bool $canView): Profile
    {
        $profile = Profile::factory()->create(['name' => "Perfil {$moduleName}"]);
        $this->assignModulePermissions($profile, $moduleName, [
            'can_view' => $canView,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => false,
        ]);

        return $profile;
    }

    private function createMember(Condominium $condominium, Profile $profile): User
    {
        $user = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $profile->id,
        ]);
        $user->condominiums()->sync([$condominium->id]);

        return $user;
    }

    private function createApartmentWithTenant(Condominium $condominium, User $tenantUser, string $number): Apartment
    {
        $block = Block::factory()->create(['condominium_id' => $condominium->id]);
        $apartment = Apartment::factory()->create([
            'condominium_id' => $condominium->id,
            'block_id' => $block->id,
            'number' => $number,
        ]);

        $tenantResident = Resident::factory()->inquilino()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $tenantUser->id,
        ]);

        $apartment->update(['tenant_id' => $tenantResident->id]);

        return $apartment->fresh();
    }

    private function createApartmentWithOwner(Condominium $condominium, User $ownerUser, string $number): Apartment
    {
        $block = Block::factory()->create(['condominium_id' => $condominium->id]);
        $apartment = Apartment::factory()->create([
            'condominium_id' => $condominium->id,
            'block_id' => $block->id,
            'number' => $number,
        ]);

        $ownerResident = Resident::factory()->proprietario()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $ownerUser->id,
        ]);

        $apartment->update(['owner_id' => $ownerResident->id]);

        return $apartment->fresh();
    }

    private function makeCommonArea(Condominium $condominium, bool $requiresApproval): CommonArea
    {
        $hourlySchedule = array_fill(0, 7, [
            'type' => CommonArea::BOOKING_TYPE_HOURLY,
            'enabled' => true,
        ]);

        return CommonArea::factory()->create([
            'condominium_id' => $condominium->id,
            'active' => true,
            'requires_approval' => $requiresApproval,
            'min_booking_hours' => 1,
            'max_booking_hours' => 8,
            'booking_schedule' => $hourlySchedule,
        ]);
    }

    private function makeBooking(
        CommonArea $commonArea,
        Apartment $apartment,
        Resident $resident,
        string $status,
        int $daysAhead,
    ): CommonAreaBooking {
        $start = now()->addDays($daysAhead)->setTime(10, 0);
        $end = now()->addDays($daysAhead)->setTime(12, 0);

        return CommonAreaBooking::factory()->create([
            'common_area_id' => $commonArea->id,
            'apartment_id' => $apartment->id,
            'resident_id' => $resident->id,
            'condominium_id' => $this->condominiumA->id,
            'status' => $status,
            'booking_date' => $start->toDateString(),
            'start_time' => $start,
            'end_time' => $end,
            'duration_hours' => 2,
        ]);
    }

    private function makeAccessRequest(
        Condominium $condominium,
        Resident $resident,
        Apartment $apartment,
        string $status
    ): AccessRequest {
        $visitor = Visitor::create([
            'condominium_id' => $condominium->id,
            'name' => 'Visitante '.uniqid(),
            'document' => 'DOC-'.uniqid(),
            'phone' => '11999999999',
        ]);

        return AccessRequest::create([
            'condominium_id' => $condominium->id,
            'visitor_id' => $visitor->id,
            'resident_id' => $resident->id,
            'apartment_id' => $apartment->id,
            'visitor_name' => $visitor->name,
            'visitor_document' => $visitor->document,
            'visitor_phone' => $visitor->phone,
            'visit_date' => now()->addDay(),
            'visit_time' => '14:00',
            'duration_hours' => 2,
            'purpose' => 'Visita',
            'status' => $status,
            'request_timestamp' => now(),
        ]);
    }
}
