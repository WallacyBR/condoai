<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestAttachment;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithTenant;
use Tests\Concerns\SeedsDefaultPermissionMatrix;
use Tests\TestCase;

class M5PolicyAuthorizationTest extends TestCase
{
    use InteractsWithTenant;
    use RefreshDatabase;
    use SeedsDefaultPermissionMatrix;

    protected Condominium $condominiumA;

    protected Condominium $condominiumB;

    protected Profile $sindicoProfile;

    protected User $userWithCrossMembership;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedDefaultPermissionMatrix();

        $this->condominiumA = Condominium::factory()->create(['name' => 'Condomínio Alpha']);
        $this->condominiumB = Condominium::factory()->create(['name' => 'Condomínio Beta']);
        $this->sindicoProfile = $this->defaultProfile('Síndico');

        $this->userWithCrossMembership = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->sindicoProfile->id,
        ]);
        $this->userWithCrossMembership->condominiums()->sync([
            $this->condominiumA->id,
            $this->condominiumB->id,
        ]);
    }

    public function test_bank_crud_allows_header_tenant_with_financeiro_permissions(): void
    {
        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumB);

        $createResponse = $this->postJson('/api/finance/banks', [
            'name' => 'Banco Tenant B',
            'account_number' => '99999-9',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumB->id);

        $bankId = $createResponse->json('id');

        $this->getJson('/api/finance/banks/'.$bankId)->assertOk();
        $this->putJson('/api/finance/banks/'.$bankId, [
            'name' => 'Banco Atualizado',
            'account_number' => '88888-8',
        ])->assertOk();
        $this->deleteJson('/api/finance/banks/'.$bankId)->assertNoContent();
    }

    public function test_bank_show_denies_when_operating_on_other_tenant_header(): void
    {
        $bankB = Bank::factory()->create([
            'condominium_id' => $this->condominiumB->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumA);

        $response = $this->getJson('/api/finance/banks/'.$bankB->id);

        $response->assertNotFound();
    }

    public function test_chart_account_store_rejects_cross_tenant_parent_id(): void
    {
        $parentB = ChartAccount::factory()->create([
            'condominium_id' => $this->condominiumB->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumA);

        $response = $this->postJson('/api/finance/chart-accounts', [
            'parent_id' => $parentB->id,
            'name' => 'Conta filha inválida',
            'type' => 'despesa',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_chart_account_crud_allows_header_tenant_with_financeiro_permissions(): void
    {
        $parent = ChartAccount::factory()->create([
            'condominium_id' => $this->condominiumB->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumB);

        $createResponse = $this->postJson('/api/finance/chart-accounts', [
            'parent_id' => $parent->id,
            'name' => 'Conta Tenant B',
            'type' => 'despesa',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumB->id);

        $chartAccountId = $createResponse->json('id');

        $this->getJson('/api/finance/chart-accounts/'.$chartAccountId)->assertOk();
        $this->putJson('/api/finance/chart-accounts/'.$chartAccountId, [
            'name' => 'Conta Atualizada',
            'type' => 'despesa',
        ])->assertOk();
        $this->deleteJson('/api/finance/chart-accounts/'.$chartAccountId)->assertNoContent();
    }

    public function test_expense_request_store_uses_header_tenant_and_auth_user_instead_of_body(): void
    {
        $otherUser = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->defaultProfile('Morador')->id,
        ]);
        $otherUser->condominiums()->sync([$this->condominiumA->id]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumB);

        $response = $this->postJson('/api/finance/expense-requests', [
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $otherUser->id,
            'amount' => 500.00,
            'due_date' => now()->addWeek()->toDateString(),
            'description' => 'Despesa no tenant B',
        ]);

        $response->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumB->id)
            ->assertJsonPath('requested_by', $this->userWithCrossMembership->id);

        $this->assertDatabaseHas('expense_requests', [
            'condominium_id' => $this->condominiumB->id,
            'requested_by' => $this->userWithCrossMembership->id,
            'amount' => 500.00,
        ]);
    }

    public function test_expense_request_show_denies_cross_tenant_resource(): void
    {
        $expenseRequestB = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'requested_by' => $this->userWithCrossMembership->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumA);

        $response = $this->getJson('/api/finance/expense-requests/'.$expenseRequestB->id);

        $response->assertNotFound();
    }

    public function test_expense_request_approve_denies_requester_auto_approval(): void
    {
        Notification::fake();

        $requester = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->sindicoProfile->id,
        ]);
        $requester->condominiums()->sync([$this->condominiumA->id]);

        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $requester->id,
        ]);

        $this->actingAsTenant($requester, $this->condominiumA);

        $response = $this->postJson('/api/finance/expense-requests/'.$expenseRequest->id.'/approve');

        $response->assertForbidden();
    }

    public function test_expense_request_update_ignores_status_in_body(): void
    {
        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $this->userWithCrossMembership->id,
            'status' => 'pendente',
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumA);

        $response = $this->putJson('/api/finance/expense-requests/'.$expenseRequest->id, [
            'status' => 'aprovado',
            'amount' => 600.00,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'pendente');

        $this->assertDatabaseHas('expense_requests', [
            'id' => $expenseRequest->id,
            'status' => 'pendente',
            'amount' => 600.00,
        ]);
    }

    public function test_expense_request_attachment_index_allows_financeiro_member_on_tenant(): void
    {
        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $this->userWithCrossMembership->id,
        ]);

        ExpenseRequestAttachment::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'expense_request_id' => $expenseRequest->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumA);

        $response = $this->getJson('/api/finance/expense-requests/'.$expenseRequest->id.'/attachments');

        $response->assertOk();
        $this->assertCount(1, $response->json());
    }

    public function test_expense_request_attachment_show_denies_cross_tenant_attachment(): void
    {
        $expenseRequestA = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $this->userWithCrossMembership->id,
        ]);

        $expenseRequestB = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'requested_by' => $this->userWithCrossMembership->id,
        ]);

        $attachmentB = ExpenseRequestAttachment::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'expense_request_id' => $expenseRequestB->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumA);

        $response = $this->getJson(
            '/api/finance/expense-requests/'.$expenseRequestA->id.'/attachments/'.$attachmentB->id
        );

        $response->assertNotFound();
    }

    public function test_morador_cannot_list_expense_request_attachments_without_financeiro_permission(): void
    {
        $moradorProfile = $this->defaultProfile('Morador');

        $user = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $moradorProfile->id,
        ]);
        $user->condominiums()->sync([$this->condominiumA->id]);

        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $user->id,
        ]);

        $this->actingAsTenant($user, $this->condominiumA);

        $response = $this->getJson('/api/finance/expense-requests/'.$expenseRequest->id.'/attachments');

        $response->assertForbidden();
    }

    public function test_expense_request_attachment_store_requires_financeiro_update_permission(): void
    {
        Storage::fake('public');

        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $this->userWithCrossMembership->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumA);

        $response = $this->postJson(
            '/api/finance/expense-requests/'.$expenseRequest->id.'/attachments',
            ['file' => UploadedFile::fake()->create('comprovante.pdf', 100, 'application/pdf')]
        );

        $response->assertCreated();
    }

    public function test_expense_request_approve_allows_sindico_for_third_party_request(): void
    {
        Notification::fake();

        $requester = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->defaultProfile('Morador')->id,
        ]);
        $requester->condominiums()->sync([$this->condominiumA->id]);

        $sindico = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->sindicoProfile->id,
        ]);
        $sindico->condominiums()->sync([$this->condominiumA->id]);

        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'requested_by' => $requester->id,
        ]);

        $this->actingAsTenant($sindico, $this->condominiumA);

        $response = $this->postJson('/api/finance/expense-requests/'.$expenseRequest->id.'/approve');

        $response->assertOk()
            ->assertJson(['message' => 'Solicitação aprovada']);

        $this->assertDatabaseHas('expense_requests', [
            'id' => $expenseRequest->id,
            'status' => 'aprovado',
            'approved_by' => $sindico->id,
        ]);
    }

    public function test_morador_cannot_list_banks_without_financeiro_permission(): void
    {
        $moradorProfile = $this->defaultProfile('Morador');

        $user = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $moradorProfile->id,
        ]);
        $user->condominiums()->sync([$this->condominiumA->id]);

        Bank::factory()->create([
            'condominium_id' => $this->condominiumA->id,
        ]);

        $this->actingAsTenant($user, $this->condominiumA);

        $response = $this->getJson('/api/finance/banks');

        $response->assertForbidden();
    }

    public function test_zelador_cannot_create_chart_account_with_read_only_financeiro_matrix(): void
    {
        $zeladorProfile = $this->defaultProfile('Zelador');

        $zelador = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $zeladorProfile->id,
        ]);
        $zelador->condominiums()->sync([$this->condominiumA->id]);

        $this->actingAsTenant($zelador, $this->condominiumA);

        $response = $this->postJson('/api/finance/chart-accounts', [
            'name' => 'Conta bloqueada',
            'type' => 'despesa',
        ]);

        $response->assertForbidden();
    }
}
