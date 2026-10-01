<?php

namespace Tests\Unit\Services\Notifications;

use App\Enums\ResidentClassification;
use App\Models\Apartment;
use App\Models\Block;
use App\Models\Condominium;
use App\Models\Profile;
use App\Models\Resident;
use App\Models\User;
use App\Services\Notifications\AudienceResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AssignsModulePermissions;
use Tests\TestCase;

class AudienceResolverTest extends TestCase
{
    use AssignsModulePermissions;
    use RefreshDatabase;

    private AudienceResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(AudienceResolver::class);
    }

    public function test_users_by_ids_drops_outsider_without_membership(): void
    {
        $condominium = Condominium::factory()->create();
        $member = User::factory()->create(['condominium_id' => $condominium->id]);
        $member->condominiums()->sync([$condominium->id]);

        $outsider = User::factory()->create();

        $users = $this->resolver->usersByIds($condominium->id, [$member->id, $outsider->id]);

        $this->assertCount(1, $users);
        $this->assertTrue($users->contains('id', $member->id));
    }

    public function test_users_by_profiles_respects_membership(): void
    {
        $condominium = Condominium::factory()->create();
        $profile = Profile::factory()->create(['name' => 'Zelador']);

        $member = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $profile->id,
        ]);
        $member->condominiums()->sync([$condominium->id]);

        $outsider = User::factory()->create(['profile_id' => $profile->id]);

        $users = $this->resolver->usersByProfiles($condominium->id, ['Zelador']);

        $this->assertCount(1, $users);
        $this->assertTrue($users->contains('id', $member->id));
        $this->assertFalse($users->contains('id', $outsider->id));
    }

    public function test_users_with_module_filters_by_can_module_and_membership(): void
    {
        $condominium = Condominium::factory()->create();
        $allowedProfile = Profile::factory()->create(['name' => 'Portaria']);
        $deniedProfile = Profile::factory()->create(['name' => 'Morador']);

        $this->assignModulePermissions($allowedProfile, 'Correspondências', [
            'can_view' => true,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
        ]);

        $staff = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $allowedProfile->id,
        ]);
        $staff->condominiums()->sync([$condominium->id]);

        $resident = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $deniedProfile->id,
        ]);
        $resident->condominiums()->sync([$condominium->id]);

        $users = $this->resolver->usersWithModule($condominium->id, 'Correspondências', 'view');

        $this->assertCount(1, $users);
        $this->assertTrue($users->contains('id', $staff->id));
    }

    public function test_single_user_is_fail_closed(): void
    {
        $condominium = Condominium::factory()->create();
        $outsider = User::factory()->create();

        $users = $this->resolver->singleUser($condominium->id, $outsider->id);

        $this->assertCount(0, $users);
    }

    public function test_users_by_membership_returns_all_pivot_users(): void
    {
        $condominium = Condominium::factory()->create();
        $first = User::factory()->create(['condominium_id' => $condominium->id]);
        $second = User::factory()->create(['condominium_id' => $condominium->id]);
        $first->condominiums()->sync([$condominium->id]);
        $second->condominiums()->sync([$condominium->id]);

        $users = $this->resolver->usersByMembership($condominium->id);

        $this->assertCount(2, $users);
    }

    public function test_users_for_apartment_includes_owner_and_tenant(): void
    {
        $condominium = Condominium::factory()->create();
        $profile = Profile::factory()->create(['name' => 'Morador']);
        $block = Block::factory()->create(['condominium_id' => $condominium->id]);

        $apartment = Apartment::factory()->create([
            'condominium_id' => $condominium->id,
            'block_id' => $block->id,
        ]);

        $ownerUser = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $profile->id,
        ]);
        $ownerUser->condominiums()->sync([$condominium->id]);

        $tenantUser = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $profile->id,
        ]);
        $tenantUser->condominiums()->sync([$condominium->id]);

        $ownerResident = Resident::factory()->proprietario()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $ownerUser->id,
        ]);
        $tenantResident = Resident::factory()->inquilino()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $tenantUser->id,
        ]);

        $apartment->update([
            'owner_id' => $ownerResident->id,
            'tenant_id' => $tenantResident->id,
        ]);

        $users = $this->resolver->usersForApartment($condominium->id, $apartment->id);

        $this->assertCount(2, $users);
        $this->assertTrue($users->contains('id', $ownerUser->id));
        $this->assertTrue($users->contains('id', $tenantUser->id));
    }

    public function test_users_for_apartment_package_audience_delegates_v4_rules(): void
    {
        $condominium = Condominium::factory()->create();
        $profile = Profile::factory()->create(['name' => 'Morador']);
        $block = Block::factory()->create(['condominium_id' => $condominium->id]);

        $apartment = Apartment::factory()->create([
            'condominium_id' => $condominium->id,
            'block_id' => $block->id,
        ]);

        $ownerUser = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $profile->id,
        ]);
        $ownerUser->condominiums()->sync([$condominium->id]);

        $tenantUser = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $profile->id,
        ]);
        $tenantUser->condominiums()->sync([$condominium->id]);

        $ownerResident = Resident::factory()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $ownerUser->id,
            'classification' => ResidentClassification::PROPRIETARIO,
        ]);
        $tenantResident = Resident::factory()->inquilino()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $tenantUser->id,
        ]);

        $apartment->update([
            'owner_id' => $ownerResident->id,
            'tenant_id' => $tenantResident->id,
        ]);

        $users = $this->resolver->usersForApartmentPackageAudience($condominium->id, $apartment->id);

        $this->assertCount(1, $users);
        $this->assertTrue($users->contains('id', $tenantUser->id));
        $this->assertFalse($users->contains('id', $ownerUser->id));
    }
}
