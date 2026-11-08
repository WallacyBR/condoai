<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\ExpenseApprovalRule;
use App\Models\Payable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\Concerns\SeedsDefaultPermissionMatrix;
use Tests\TestCase;

class FinanceOperationalTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase, SeedsDefaultPermissionMatrix;

    private Condominium $condominium;

    private User $user;

    private ChartAccount $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedDefaultPermissionMatrix();
        $this->condominium = Condominium::factory()->create();
        $this->user = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'profile_id' => $this->defaultProfile('Síndico')->id,
        ]);
        $this->account = ChartAccount::factory()->create(['condominium_id' => $this->condominium->id]);
        $this->actingAsTenant($this->user, $this->condominium);
    }

    public function test_creates_and_partially_settles_tenant_payable(): void
    {
        $bank = Bank::factory()->create(['condominium_id' => $this->condominium->id]);
        $payable = $this->postJson('/api/finance/payables', [
            'chart_account_id' => $this->account->id,
            'bank_id' => $bank->id,
            'due_date' => '2026-07-10',
            'competence_date' => '2026-07-01',
            'amount' => 1000,
            'description' => 'Reparo hidráulico',
        ])->assertCreated()->json();

        $this->postJson("/api/finance/payables/{$payable['id']}/settlements", [
            'bank_id' => $bank->id,
            'settled_on' => '2026-07-05',
            'amount' => 400,
        ])->assertCreated();

        $this->assertDatabaseHas('financial_settlements', ['payable_id' => $payable['id'], 'amount' => 400]);
        $this->assertDatabaseHas('payables', ['id' => $payable['id'], 'status' => 'partially_paid']);
    }

    public function test_finance_reports_return_json_and_pdf_for_current_tenant(): void
    {
        Payable::factory()->create(['condominium_id' => $this->condominium->id, 'chart_account_id' => $this->account->id, 'due_date' => '2026-06-10', 'competence_date' => '2026-06-10', 'amount' => 250]);

        $this->getJson('/api/finance/reports/dre?start_date=2026-06-01&end_date=2026-06-30')
            ->assertOk()->assertJsonPath('kind', 'gerencial_competencia')->assertJsonPath('totals.expenses', 250);
        $this->withTenantHeader($this->condominium)
            ->get('/api/finance/reports/balancete?as_of_date=2026-06-30&format=pdf')
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_expense_request_requires_other_configured_approver_then_converts_manually(): void
    {
        $approver = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'profile_id' => $this->defaultProfile('Síndico')->id,
        ]);
        ExpenseApprovalRule::create(['condominium_id' => $this->condominium->id, 'user_id' => $approver->id, 'minimum_amount' => 0, 'approval_order' => 1]);
        $request = $this->postJson('/api/finance/expense-requests', ['title' => 'Compra de material', 'amount' => 300, 'due_date' => '2026-07-15', 'chart_account_id' => $this->account->id])->assertCreated()->json();
        $this->postJson("/api/finance/expense-requests/{$request['id']}/submit")->assertOk()->assertJsonPath('status', 'in_approval');
        $this->actingAsTenant($approver, $this->condominium)->postJson("/api/finance/expense-requests/{$request['id']}/decision", ['decision' => 'approved', 'justification' => 'Dentro do orçamento aprovado.'])->assertOk()->assertJsonPath('status', 'approved');
        $this->actingAsTenant($this->user, $this->condominium)->postJson("/api/finance/expense-requests/{$request['id']}/convert-to-payable")->assertCreated();
        $this->assertDatabaseHas('expense_requests', ['id' => $request['id'], 'status' => 'converted']);
    }

    public function test_public_supplier_quote_uses_only_its_invitation_token(): void
    {
        $procurement = $this->postJson('/api/finance/procurements', ['title' => 'Pintura do hall', 'response_deadline' => now()->addDays(5)->toDateString(), 'items' => [['description' => 'Tinta acrílica', 'unit' => 'lata', 'quantity' => 4]]])->assertCreated()->json();
        $this->postJson("/api/finance/procurements/{$procurement['id']}/publish")->assertOk();
        $invitation = $this->postJson("/api/finance/procurements/{$procurement['id']}/invitations", ['supplier_name' => 'Fornecedor Exemplo'])->assertCreated()->json();
        $token = basename(parse_url($invitation['response_url'], PHP_URL_PATH));

        $publicData = $this->getJson("/api/public/supplier-quotes/{$token}")->assertOk()->json();
        $this->postJson("/api/public/supplier-quotes/{$token}", ['supplier_name' => 'Fornecedor Exemplo', 'submit' => true, 'items' => [['procurement_item_id' => $publicData['items'][0]['id'], 'unit_price' => 120]]])->assertCreated();
        $this->assertDatabaseHas('supplier_quotes', ['supplier_name' => 'Fornecedor Exemplo', 'status' => 'submitted']);
    }

    public function test_procurement_rejects_non_lowest_quote_when_lowest_total_is_selected(): void
    {
        $procurement = $this->postJson('/api/finance/procurements', [
            'title' => 'Material elétrico',
            'response_deadline' => now()->addDays(5)->toDateString(),
            'items' => [['description' => 'Lâmpada', 'unit' => 'un', 'quantity' => 2]],
        ])->assertCreated()->json();
        $this->postJson("/api/finance/procurements/{$procurement['id']}/publish")->assertOk();

        $first = $this->createSubmittedSupplierQuote($procurement['id'], 'Fornecedor A', 80);
        $second = $this->createSubmittedSupplierQuote($procurement['id'], 'Fornecedor B', 120);

        $this->postJson("/api/finance/procurements/{$procurement['id']}/close", [
            'criterion' => 'lowest_total',
            'publish_result' => false,
            'awards' => [['supplier_quote_id' => $second]],
        ])->assertUnprocessable()->assertJsonValidationErrors('awards');

        $this->postJson("/api/finance/procurements/{$procurement['id']}/close", [
            'criterion' => 'lowest_total',
            'publish_result' => false,
            'awards' => [['supplier_quote_id' => $first]],
        ])->assertOk()->assertJsonPath('status', 'closed');
    }

    private function createSubmittedSupplierQuote(int $procurementId, string $supplierName, float $unitPrice): int
    {
        $invitation = $this->postJson("/api/finance/procurements/{$procurementId}/invitations", ['supplier_name' => $supplierName])->assertCreated()->json();
        $token = basename(parse_url($invitation['response_url'], PHP_URL_PATH));
        $items = $this->getJson("/api/public/supplier-quotes/{$token}")->assertOk()->json('items');

        $this->postJson("/api/public/supplier-quotes/{$token}", [
            'supplier_name' => $supplierName,
            'submit' => true,
            'items' => [['procurement_item_id' => $items[0]['id'], 'unit_price' => $unitPrice]],
        ])->assertCreated();

        return (int) \App\Models\SupplierQuote::where('supplier_name', $supplierName)->value('id');
    }
}
