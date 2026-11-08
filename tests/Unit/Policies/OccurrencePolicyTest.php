<?php

namespace Tests\Unit\Policies;

use App\Models\Condominium;
use App\Models\Occurrence;
use App\Models\User;
use App\Policies\OccurrencePolicy;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsDefaultPermissionMatrix;
use Tests\TestCase;

class OccurrencePolicyTest extends TestCase
{
    use RefreshDatabase;
    use SeedsDefaultPermissionMatrix;

    private OccurrencePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedDefaultPermissionMatrix();
        $this->policy = new OccurrencePolicy;
    }

    protected function tearDown(): void
    {
        TenantContext::clear();

        parent::tearDown();
    }

    public function test_sindico_can_view_third_party_occurrence_with_seeded_matrix(): void
    {
        $condominiumA = Condominium::factory()->create();
        $condominiumB = Condominium::factory()->create();
        $sindicoProfile = $this->defaultProfile('Síndico');

        $sindico = User::factory()->create([
            'condominium_id' => $condominiumA->id,
            'profile_id' => $sindicoProfile->id,
        ]);
        $sindico->condominiums()->sync([$condominiumA->id, $condominiumB->id]);

        $otherUser = User::factory()->create([
            'condominium_id' => $condominiumB->id,
            'profile_id' => $this->defaultProfile('Morador')->id,
        ]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $condominiumB->id,
            'user_id' => $otherUser->id,
        ]);

        TenantContext::set($condominiumB);

        $this->assertTrue($this->policy->view($sindico, $occurrence));
    }

    public function test_sindico_can_update_third_party_occurrence_with_seeded_matrix(): void
    {
        $condominium = Condominium::factory()->create();
        $sindicoProfile = $this->defaultProfile('Síndico');

        $sindico = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $sindicoProfile->id,
        ]);
        $sindico->condominiums()->sync([$condominium->id]);

        $otherUser = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $this->defaultProfile('Morador')->id,
        ]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $otherUser->id,
        ]);

        TenantContext::set($condominium);

        $this->assertTrue($this->policy->update($sindico, $occurrence));
    }

    public function test_view_denies_resource_from_other_tenant(): void
    {
        $condominiumA = Condominium::factory()->create();
        $condominiumB = Condominium::factory()->create();
        $sindicoProfile = $this->defaultProfile('Síndico');

        $sindico = User::factory()->create([
            'condominium_id' => $condominiumA->id,
            'profile_id' => $sindicoProfile->id,
        ]);
        $sindico->condominiums()->sync([$condominiumA->id, $condominiumB->id]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $condominiumB->id,
        ]);

        TenantContext::set($condominiumA);

        $this->assertFalse($this->policy->view($sindico, $occurrence));
    }

    public function test_morador_author_can_update_own_occurrence_with_seeded_matrix(): void
    {
        $condominium = Condominium::factory()->create();
        $moradorProfile = $this->defaultProfile('Morador');

        $user = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $moradorProfile->id,
        ]);
        $user->condominiums()->sync([$condominium->id]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $user->id,
        ]);

        TenantContext::set($condominium);

        $this->assertTrue($this->policy->update($user, $occurrence));
    }

    public function test_zelador_cannot_update_third_party_occurrence_with_seeded_read_only_matrix(): void
    {
        $condominium = Condominium::factory()->create();
        $zeladorProfile = $this->defaultProfile('Zelador');

        $zelador = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $zeladorProfile->id,
        ]);
        $zelador->condominiums()->sync([$condominium->id]);

        $otherUser = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $this->defaultProfile('Morador')->id,
        ]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $otherUser->id,
        ]);

        TenantContext::set($condominium);

        $this->assertFalse($this->policy->update($zelador, $occurrence));
    }
}
