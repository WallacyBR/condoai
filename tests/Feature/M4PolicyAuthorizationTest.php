<?php

namespace Tests\Feature;

use App\Enums\ModuleName;
use App\Models\Bank;
use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\Occurrence;
use App\Models\Payable;
use App\Models\Profile;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AssignsModulePermissions;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class M4PolicyAuthorizationTest extends TestCase
{
    use AssignsModulePermissions;
    use InteractsWithTenant;
    use RefreshDatabase;

    protected Condominium $condominiumA;

    protected Condominium $condominiumB;

    protected Profile $sindicoProfile;

    protected User $userWithCrossMembership;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominiumA = Condominium::factory()->create(['name' => 'Condomínio Alpha']);
        $this->condominiumB = Condominium::factory()->create(['name' => 'Condomínio Beta']);
        $this->sindicoProfile = Profile::factory()->create(['name' => 'Síndico']);

        $this->assignModulePermissions($this->sindicoProfile, ModuleName::Ocorrencias->value, [
            'can_view' => true,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => true,
            'scope' => 'condominium',
        ]);
        $this->assignModulePermissions($this->sindicoProfile, ModuleName::Financeiro->value, [
            'can_view' => true,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => true,
            'scope' => 'condominium',
        ]);

        $this->userWithCrossMembership = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->sindicoProfile->id,
        ]);
        $this->userWithCrossMembership->condominiums()->sync([
            $this->condominiumA->id,
            $this->condominiumB->id,
        ]);
    }

    public function test_occurrence_index_allows_membership_on_header_tenant_even_with_different_home(): void
    {
        Occurrence::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'user_id' => $this->userWithCrossMembership->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumB);

        $response = $this->getJson('/api/occurrences');

        $response->assertOk();
        $this->assertCount(1, $response->json('data') ?? $response->json());
    }

    public function test_occurrence_store_uses_header_tenant_instead_of_home_condominium(): void
    {
        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumB);

        $response = $this->postJson('/api/occurrences', [
            'title' => 'Ocorrência no tenant B',
            'description' => 'Descrição da ocorrência',
            'priority' => 'medium',
        ]);

        $response->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumB->id);

        $this->assertDatabaseHas('occurrences', [
            'title' => 'Ocorrência no tenant B',
            'condominium_id' => $this->condominiumB->id,
        ]);
    }

    public function test_occurrence_author_can_update_own_record_without_module_update_permission(): void
    {
        $moradorProfile = Profile::factory()->create(['name' => 'Morador']);
        $this->assignModulePermissions($moradorProfile, ModuleName::Ocorrencias->value, [
            'can_view' => true,
            'can_create' => true,
            'can_update' => false,
            'can_delete' => false,
            'scope' => 'own',
        ]);

        $author = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $moradorProfile->id,
        ]);
        $author->condominiums()->sync([$this->condominiumA->id]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'user_id' => $author->id,
        ]);

        $this->actingAsTenant($author, $this->condominiumA);

        $response = $this->putJson('/api/occurrences/'.$occurrence->id, [
            'title' => 'Título atualizado pelo autor',
        ]);

        $response->assertOk()
            ->assertJsonPath('title', 'Título atualizado pelo autor');
    }

    public function test_occurrence_show_denies_cross_tenant_resource(): void
    {
        $occurrenceB = Occurrence::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'user_id' => $this->userWithCrossMembership->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumA);

        $response = $this->getJson('/api/occurrences/'.$occurrenceB->id);

        $response->assertNotFound();
    }

    public function test_occurrence_comment_create_requires_create_permission(): void
    {
        $readOnlyProfile = Profile::factory()->create(['name' => 'Leitor']);
        $this->assignModulePermissions($readOnlyProfile, ModuleName::Ocorrencias->value, [
            'can_view' => true,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'scope' => 'condominium',
        ]);

        $reader = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $readOnlyProfile->id,
        ]);
        $reader->condominiums()->sync([$this->condominiumA->id]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'user_id' => $reader->id,
        ]);

        $this->actingAsTenant($reader, $this->condominiumA);

        $response = $this->postJson('/api/occurrences/'.$occurrence->id.'/comments', [
            'comment' => 'Comentário bloqueado',
        ]);

        $response->assertForbidden();
    }

    public function test_payable_store_rejects_cross_tenant_chart_account(): void
    {
        $chartAccountB = ChartAccount::factory()->create([
            'condominium_id' => $this->condominiumB->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumA);

        $response = $this->postJson('/api/finance/payables', [
            'chart_account_id' => $chartAccountB->id,
            'due_date' => now()->addWeek()->toDateString(),
            'amount' => 150.00,
            'status' => 'pendente',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['chart_account_id']);
    }

    public function test_payable_crud_allows_header_tenant_with_financeiro_permissions(): void
    {
        $chartAccount = ChartAccount::factory()->create([
            'condominium_id' => $this->condominiumB->id,
        ]);
        $bank = Bank::factory()->create([
            'condominium_id' => $this->condominiumB->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumB);

        $createResponse = $this->postJson('/api/finance/payables', [
            'chart_account_id' => $chartAccount->id,
            'bank_id' => $bank->id,
            'due_date' => now()->addWeek()->toDateString(),
            'amount' => 250.00,
            'status' => 'pendente',
            'description' => 'Conta no tenant B',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('condominium_id', $this->condominiumB->id);

        $payableId = $createResponse->json('id');

        $this->getJson('/api/finance/payables/'.$payableId)->assertOk();
        $this->putJson('/api/finance/payables/'.$payableId, [
            'chart_account_id' => $chartAccount->id,
            'bank_id' => $bank->id,
            'due_date' => now()->addWeek()->toDateString(),
            'amount' => 300.00,
            'status' => 'pendente',
        ])->assertOk();
        $this->deleteJson('/api/finance/payables/'.$payableId)->assertNoContent();
    }

    public function test_payable_show_denies_when_operating_on_other_tenant_header(): void
    {
        $payableB = Payable::factory()->create([
            'condominium_id' => $this->condominiumB->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumA);

        $response = $this->getJson('/api/finance/payables/'.$payableB->id);

        $response->assertNotFound();
    }

    public function test_receivable_store_rejects_cross_tenant_bank(): void
    {
        $chartAccount = ChartAccount::factory()->create([
            'condominium_id' => $this->condominiumA->id,
        ]);
        $bankB = Bank::factory()->create([
            'condominium_id' => $this->condominiumB->id,
        ]);

        $this->actingAsTenant($this->userWithCrossMembership, $this->condominiumA);

        $response = $this->postJson('/api/finance/receivables', [
            'chart_account_id' => $chartAccount->id,
            'bank_id' => $bankB->id,
            'due_date' => now()->addWeek()->toDateString(),
            'amount' => 99.00,
            'status' => 'pendente',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['bank_id']);
    }

    public function test_receivable_index_denies_without_financeiro_view_permission(): void
    {
        $readOnlyProfile = Profile::factory()->create(['name' => 'Sem Financeiro']);
        $this->assignModulePermissions($readOnlyProfile, ModuleName::Ocorrencias->value, [
            'can_view' => true,
            'scope' => 'condominium',
        ]);

        $user = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $readOnlyProfile->id,
        ]);
        $user->condominiums()->sync([$this->condominiumA->id]);

        Receivable::factory()->create([
            'condominium_id' => $this->condominiumA->id,
        ]);

        $this->actingAsTenant($user, $this->condominiumA);

        $response = $this->getJson('/api/finance/receivables');

        $response->assertForbidden();
    }
}
