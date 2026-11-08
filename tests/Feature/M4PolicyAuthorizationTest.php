<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\ChartAccount;
use App\Models\Condominium;
use App\Models\Occurrence;
use App\Models\Payable;
use App\Models\Profile;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\Concerns\SeedsDefaultPermissionMatrix;
use Tests\TestCase;

class M4PolicyAuthorizationTest extends TestCase
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

    public function test_sindico_can_update_third_party_occurrence_with_seeded_matrix(): void
    {
        $otherUser = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->defaultProfile('Morador')->id,
        ]);
        $otherUser->condominiums()->sync([$this->condominiumA->id]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'user_id' => $otherUser->id,
            'title' => 'Ocorrência de terceiro',
        ]);

        $sindico = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->sindicoProfile->id,
        ]);
        $sindico->condominiums()->sync([$this->condominiumA->id]);

        $this->actingAsTenant($sindico, $this->condominiumA);

        $response = $this->putJson('/api/occurrences/'.$occurrence->id, [
            'title' => 'Atualizada pelo síndico',
        ]);

        $response->assertOk()
            ->assertJsonPath('title', 'Atualizada pelo síndico');
    }

    public function test_occurrence_author_can_update_own_record_without_module_update_permission(): void
    {
        $moradorProfile = $this->defaultProfile('Morador');

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

    public function test_zelador_cannot_create_occurrence_comment_with_seeded_read_only_matrix(): void
    {
        $zeladorProfile = $this->defaultProfile('Zelador');

        $zelador = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $zeladorProfile->id,
        ]);
        $zelador->condominiums()->sync([$this->condominiumA->id]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'user_id' => $zelador->id,
        ]);

        $this->actingAsTenant($zelador, $this->condominiumA);

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

    public function test_morador_cannot_list_receivables_without_financeiro_permission(): void
    {
        $moradorProfile = $this->defaultProfile('Morador');

        $user = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $moradorProfile->id,
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
