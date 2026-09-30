<?php

namespace Tests\Unit\Policies;

use App\Enums\ModuleName;
use App\Models\Condominium;
use App\Models\Occurrence;
use App\Models\Profile;
use App\Models\User;
use App\Policies\OccurrencePolicy;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AssignsModulePermissions;
use Tests\TestCase;

class OccurrencePolicyTest extends TestCase
{
    use AssignsModulePermissions;
    use RefreshDatabase;

    private OccurrencePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new OccurrencePolicy();
    }

    protected function tearDown(): void
    {
        TenantContext::clear();

        parent::tearDown();
    }

    public function test_view_allows_module_permission_on_active_tenant_even_when_home_differs(): void
    {
        $condominiumA = Condominium::factory()->create();
        $condominiumB = Condominium::factory()->create();
        $profile = Profile::factory()->create(['name' => 'Síndico']);

        $this->assignModulePermissions($profile, ModuleName::Ocorrencias->value, [
            'can_view' => true,
            'scope' => 'condominium',
        ]);

        $user = User::factory()->create([
            'condominium_id' => $condominiumA->id,
            'profile_id' => $profile->id,
        ]);
        $user->condominiums()->sync([$condominiumA->id, $condominiumB->id]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $condominiumB->id,
            'user_id' => $user->id,
        ]);

        TenantContext::set($condominiumB);

        $this->assertTrue($this->policy->view($user, $occurrence));
    }

    public function test_view_denies_resource_from_other_tenant(): void
    {
        $condominiumA = Condominium::factory()->create();
        $condominiumB = Condominium::factory()->create();
        $profile = Profile::factory()->create(['name' => 'Síndico']);

        $this->assignModulePermissions($profile, ModuleName::Ocorrencias->value, [
            'can_view' => true,
            'scope' => 'condominium',
        ]);

        $user = User::factory()->create([
            'condominium_id' => $condominiumA->id,
            'profile_id' => $profile->id,
        ]);
        $user->condominiums()->sync([$condominiumA->id, $condominiumB->id]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $condominiumB->id,
        ]);

        TenantContext::set($condominiumA);

        $this->assertFalse($this->policy->view($user, $occurrence));
    }

    public function test_update_always_allows_author_on_active_tenant(): void
    {
        $condominium = Condominium::factory()->create();
        $profile = Profile::factory()->create(['name' => 'Morador']);

        $this->assignModulePermissions($profile, ModuleName::Ocorrencias->value, [
            'can_update' => false,
            'scope' => 'own',
        ]);

        $user = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $profile->id,
        ]);
        $user->condominiums()->sync([$condominium->id]);

        $occurrence = Occurrence::factory()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $user->id,
        ]);

        TenantContext::set($condominium);

        $this->assertTrue($this->policy->update($user, $occurrence));
    }
}
