<?php

namespace Tests\Feature\Notifications;

use App\Enums\OccurrencePriority;
use App\Enums\ResidentClassification;
use App\Events\Finance\ExpenseRequestWasApproved;
use App\Events\Notices\NoticeWasPublished;
use App\Events\Occurrences\OccurrenceWasCreated;
use App\Events\Packages\PackageWasReceived;
use App\Models\Apartment;
use App\Models\Block;
use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\ExpenseApprovalRule;
use App\Models\ExpenseRequest;
use App\Models\Profile;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\AssignsModulePermissions;
use Tests\Concerns\InteractsWithTenant;
use Tests\Concerns\SeedsDefaultPermissionMatrix;
use Tests\TestCase;

class E3NotificationEmittersTest extends TestCase
{
    use AssignsModulePermissions;
    use InteractsWithTenant;
    use RefreshDatabase;
    use SeedsDefaultPermissionMatrix;

    protected Condominium $condominiumA;

    protected Condominium $condominiumB;

    protected Profile $sindicoProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedDefaultPermissionMatrix();

        $this->condominiumA = Condominium::factory()->create(['name' => 'Condomínio Alpha']);
        $this->condominiumB = Condominium::factory()->create(['name' => 'Condomínio Beta']);
        $this->sindicoProfile = $this->defaultProfile('Síndico');
    }

    public function test_expense_approve_notifies_requester_not_approver(): void
    {
        $requester = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $approver = $this->createMember($this->condominiumA, $this->sindicoProfile);

        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $requester->id,
            'title' => 'Material de limpeza',
            'amount' => 150.50,
        ]);

        $this->actingAsTenant($approver, $this->condominiumA)
            ->postJson("/api/finance/expense-requests/{$expenseRequest->id}/approve")
            ->assertOk();

        $this->assertSame(1, $requester->fresh()->notifications()->count());
        $this->assertSame(0, $approver->fresh()->notifications()->count());

        $notification = $requester->notifications()->first();
        $this->assertSame($this->condominiumA->id, $notification->data['condominium_id']);
        $this->assertSame('finance', $notification->data['category']);
        $this->assertSame('Despesa aprovada', $notification->data['title']);
        $this->assertSame("expense_request.approved:{$expenseRequest->id}", $notification->data['dedupe_key']);
        $this->assertSame("/finance/expense-requests/{$expenseRequest->id}", $notification->data['action_url']);
        $this->assertSame('150.50', $notification->data['amount']);
        $this->assertNotNull($notification->data['approved_at']);
    }

    public function test_expense_decide_final_notifies_requester_intermediate_creates_no_rows(): void
    {
        $requester = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $firstApprover = $this->createMember($this->condominiumA, $this->sindicoProfile);
        $secondApprover = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->sindicoProfile->id,
        ]);
        $secondApprover->condominiums()->sync([$this->condominiumA->id]);

        ChartAccount::factory()->create(['condominium_id' => $this->condominiumA->id]);

        ExpenseApprovalRule::create([
            'condominium_id' => $this->condominiumA->id,
            'user_id' => $firstApprover->id,
            'minimum_amount' => 0,
            'approval_order' => 1,
            'active' => true,
        ]);
        ExpenseApprovalRule::create([
            'condominium_id' => $this->condominiumA->id,
            'user_id' => $secondApprover->id,
            'minimum_amount' => 0,
            'approval_order' => 2,
            'active' => true,
        ]);

        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $requester->id,
            'status' => 'draft',
            'amount' => 500,
        ]);

        $this->actingAsTenant($requester, $this->condominiumA)
            ->postJson("/api/finance/expense-requests/{$expenseRequest->id}/submit")
            ->assertOk();

        $this->actingAsTenant($firstApprover, $this->condominiumA)
            ->postJson("/api/finance/expense-requests/{$expenseRequest->id}/decision", [
                'decision' => 'approved',
                'justification' => 'Primeira aprovação.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'in_approval');

        $this->assertSame(0, $requester->fresh()->notifications()->count());

        $this->actingAsTenant($secondApprover, $this->condominiumA)
            ->postJson("/api/finance/expense-requests/{$expenseRequest->id}/decision", [
                'decision' => 'approved',
                'justification' => 'Aprovação final.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'approved');

        $this->assertSame(1, $requester->fresh()->notifications()->count());
        $this->assertSame(0, $secondApprover->fresh()->notifications()->count());
    }

    public function test_package_notifies_unit_resident_and_correspondencias_staff(): void
    {
        $staffProfile = Profile::factory()->create(['name' => 'Portaria']);
        $this->assignModulePermissions($staffProfile, 'Correspondências', [
            'can_view' => true,
            'can_create' => true,
            'can_update' => false,
            'can_delete' => false,
        ]);

        $outsiderProfile = Profile::factory()->create(['name' => 'Morador sem Correspondências']);
        $this->assignModulePermissions($outsiderProfile, 'Ocorrências', [
            'can_view' => true,
            'can_create' => true,
            'can_update' => false,
            'can_delete' => false,
        ]);

        $staff = $this->createMember($this->condominiumA, $staffProfile);
        $tenantUser = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $otherUnitUser = $this->createMember($this->condominiumA, $outsiderProfile);
        $condoBOnlyUser = $this->createMember($this->condominiumB, $outsiderProfile);

        $targetApartment = $this->createApartmentWithTenant($this->condominiumA, $tenantUser, '101');
        $otherApartment = $this->createApartmentWithTenant($this->condominiumA, $otherUnitUser, '202');

        $this->actingAsTenant($staff, $this->condominiumA)
            ->postJson('/api/packages', [
                'apartment_id' => $targetApartment->id,
                'pickup_location' => 'Portaria',
            ])
            ->assertCreated();

        $this->assertSame(1, $tenantUser->fresh()->notifications()->count());
        $this->assertSame(0, $otherUnitUser->fresh()->notifications()->count());
        $this->assertSame(1, $staff->fresh()->notifications()->count());
        $this->assertSame(0, $condoBOnlyUser->fresh()->notifications()->count());

        $notification = $tenantUser->notifications()->first();
        $this->assertSame('Nova encomenda', $notification->data['title']);
        $this->assertSame('packages', $notification->data['category']);
        $this->assertStringContainsString('101', $notification->data['body']);
    }

    public function test_notice_send_to_all_notifies_all_membership_not_other_condo(): void
    {
        $publisher = $this->createMember($this->condominiumA, $this->sindicoProfile);
        $memberA = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $memberBOnly = $this->createMember($this->condominiumB, $this->defaultProfile('Morador'));

        $this->actingAsTenant($publisher, $this->condominiumA)
            ->postJson('/api/notices', [
                'title' => 'Assembleia geral',
                'body' => 'Reunião na próxima semana.',
                'send_to_all' => true,
            ])
            ->assertCreated();

        $this->assertGreaterThanOrEqual(1, $memberA->fresh()->notifications()->count());
        $this->assertSame(0, $memberBOnly->fresh()->notifications()->count());

        $notification = $memberA->notifications()->first();
        $this->assertSame('Assembleia geral', $notification->data['title']);
        $this->assertSame('notices', $notification->data['category']);
    }

    public function test_notice_apartment_target_notifies_only_targeted_unit_users(): void
    {
        $publisher = $this->createMember($this->condominiumA, $this->sindicoProfile);
        $targetUser = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $otherUser = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));

        $targetApartment = $this->createApartmentWithOwner($this->condominiumA, $targetUser, '301');
        $this->createApartmentWithOwner($this->condominiumA, $otherUser, '302');

        $this->actingAsTenant($publisher, $this->condominiumA)
            ->postJson('/api/notices', [
                'title' => 'Aviso unidade 301',
                'body' => 'Manutenção programada.',
                'send_to_all' => false,
                'targets' => [
                    ['type' => 'apartment', 'id' => $targetApartment->id],
                ],
            ])
            ->assertCreated();

        $this->assertSame(1, $targetUser->fresh()->notifications()->count());
        $this->assertSame(0, $otherUser->fresh()->notifications()->count());
    }

    public function test_occurrence_notifies_staff_excludes_author(): void
    {
        $staffProfile = Profile::factory()->create(['name' => 'Zelador Ocorrências']);
        $this->assignModulePermissions($staffProfile, 'Ocorrências', [
            'can_view' => true,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
        ]);

        $authorProfile = Profile::factory()->create(['name' => 'Morador Ocorrências']);
        $this->assignModulePermissions($authorProfile, 'Ocorrências', [
            'can_view' => true,
            'can_create' => true,
            'can_update' => false,
            'can_delete' => false,
        ]);

        $outsiderProfile = Profile::factory()->create(['name' => 'Sem Ocorrências']);
        $this->assignModulePermissions($outsiderProfile, 'Correspondências', [
            'can_view' => true,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
        ]);

        $staff = $this->createMember($this->condominiumA, $staffProfile);
        $author = $this->createMember($this->condominiumA, $authorProfile);
        $outsider = $this->createMember($this->condominiumA, $outsiderProfile);

        $this->actingAsTenant($author, $this->condominiumA)
            ->postJson('/api/occurrences', [
                'title' => 'Vazamento hall',
                'description' => 'Água no corredor.',
                'priority' => OccurrencePriority::HIGH->value,
            ])
            ->assertCreated();

        $this->assertSame(1, $staff->fresh()->notifications()->count());
        $this->assertSame(0, $author->fresh()->notifications()->count());
        $this->assertSame(0, $outsider->fresh()->notifications()->count());

        $notification = $staff->notifications()->first();
        $this->assertSame('Vazamento hall', $notification->data['title']);
        $this->assertSame('occurrences', $notification->data['category']);
        $this->assertSame(OccurrencePriority::HIGH->value, $notification->data['priority']);
    }

    public function test_dedupe_prevents_second_notification_for_same_entity(): void
    {
        $requester = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));

        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $requester->id,
            'approved_at' => now(),
        ]);

        event(new ExpenseRequestWasApproved(
            condominiumId: $this->condominiumA->id,
            actorId: null,
            expenseRequestId: $expenseRequest->id,
        ));

        event(new ExpenseRequestWasApproved(
            condominiumId: $this->condominiumA->id,
            actorId: null,
            expenseRequestId: $expenseRequest->id,
        ));

        $this->assertSame(1, $requester->fresh()->notifications()->count());
    }

    public function test_expense_approve_controller_dispatches_event(): void
    {
        Event::fake([ExpenseRequestWasApproved::class]);

        $requester = $this->createMember($this->condominiumA, $this->defaultProfile('Morador'));
        $approver = $this->createMember($this->condominiumA, $this->sindicoProfile);

        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $requester->id,
        ]);

        $this->actingAsTenant($approver, $this->condominiumA)
            ->postJson("/api/finance/expense-requests/{$expenseRequest->id}/approve")
            ->assertOk();

        Event::assertDispatched(ExpenseRequestWasApproved::class, function (ExpenseRequestWasApproved $event) use ($expenseRequest, $approver): bool {
            return $event->condominiumId === $this->condominiumA->id
                && $event->actorId === $approver->id
                && $event->expenseRequestId === $expenseRequest->id;
        });
    }

    public function test_package_store_dispatches_event(): void
    {
        Event::fake([PackageWasReceived::class]);

        $staff = $this->createMember($this->condominiumA, $this->sindicoProfile);
        $apartment = $this->createApartmentWithTenant($this->condominiumA, $staff, '101');

        $this->actingAsTenant($staff, $this->condominiumA)
            ->postJson('/api/packages', [
                'apartment_id' => $apartment->id,
                'pickup_location' => 'Portaria',
            ])
            ->assertCreated();

        Event::assertDispatched(PackageWasReceived::class);
    }

    public function test_notice_store_dispatches_event(): void
    {
        Event::fake([NoticeWasPublished::class]);

        $publisher = $this->createMember($this->condominiumA, $this->sindicoProfile);

        $this->actingAsTenant($publisher, $this->condominiumA)
            ->postJson('/api/notices', [
                'title' => 'Teste',
                'body' => 'Corpo',
                'send_to_all' => true,
            ])
            ->assertCreated();

        Event::assertDispatched(NoticeWasPublished::class);
    }

    public function test_occurrence_store_dispatches_event(): void
    {
        Event::fake([OccurrenceWasCreated::class]);

        $authorProfile = Profile::factory()->create(['name' => 'Autor Ocorrência']);
        $this->assignModulePermissions($authorProfile, 'Ocorrências', [
            'can_view' => true,
            'can_create' => true,
            'can_update' => false,
            'can_delete' => false,
        ]);

        $author = $this->createMember($this->condominiumA, $authorProfile);

        $this->actingAsTenant($author, $this->condominiumA)
            ->postJson('/api/occurrences', [
                'title' => 'Teste evento',
                'description' => 'Descrição.',
                'priority' => OccurrencePriority::MEDIUM->value,
            ])
            ->assertCreated();

        Event::assertDispatched(OccurrenceWasCreated::class);
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
}
