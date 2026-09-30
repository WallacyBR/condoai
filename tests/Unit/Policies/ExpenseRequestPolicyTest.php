<?php

namespace Tests\Unit\Policies;

use App\Models\Condominium;
use App\Models\ExpenseRequest;
use App\Models\User;
use App\Policies\ExpenseRequestPolicy;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsDefaultPermissionMatrix;
use Tests\TestCase;

class ExpenseRequestPolicyTest extends TestCase
{
    use RefreshDatabase;
    use SeedsDefaultPermissionMatrix;

    private ExpenseRequestPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedDefaultPermissionMatrix();
        $this->policy = new ExpenseRequestPolicy();
    }

    protected function tearDown(): void
    {
        TenantContext::clear();

        parent::tearDown();
    }

    public function test_sindico_can_approve_third_party_expense_request(): void
    {
        $condominium = Condominium::factory()->create();
        $sindicoProfile = $this->defaultProfile('Síndico');

        $sindico = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $sindicoProfile->id,
        ]);
        $sindico->condominiums()->sync([$condominium->id]);

        $requester = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $this->defaultProfile('Morador')->id,
        ]);

        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $condominium->id,
            'requested_by' => $requester->id,
        ]);

        TenantContext::set($condominium);

        $this->assertTrue($this->policy->approve($sindico, $expenseRequest));
    }

    public function test_requester_cannot_approve_own_expense_request(): void
    {
        $condominium = Condominium::factory()->create();
        $sindicoProfile = $this->defaultProfile('Síndico');

        $requester = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $sindicoProfile->id,
        ]);
        $requester->condominiums()->sync([$condominium->id]);

        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $condominium->id,
            'requested_by' => $requester->id,
        ]);

        TenantContext::set($condominium);

        $this->assertFalse($this->policy->approve($requester, $expenseRequest));
    }

    public function test_approve_denies_cross_tenant_expense_request(): void
    {
        $condominiumA = Condominium::factory()->create();
        $condominiumB = Condominium::factory()->create();
        $sindicoProfile = $this->defaultProfile('Síndico');

        $sindico = User::factory()->create([
            'condominium_id' => $condominiumA->id,
            'profile_id' => $sindicoProfile->id,
        ]);
        $sindico->condominiums()->sync([$condominiumA->id, $condominiumB->id]);

        $requester = User::factory()->create([
            'condominium_id' => $condominiumB->id,
            'profile_id' => $this->defaultProfile('Morador')->id,
        ]);

        $expenseRequest = ExpenseRequest::factory()->create([
            'condominium_id' => $condominiumB->id,
            'requested_by' => $requester->id,
        ]);

        TenantContext::set($condominiumA);

        $this->assertFalse($this->policy->approve($sindico, $expenseRequest));
    }
}
