<?php

namespace Tests\Unit\Services;

use App\Enums\ResidentClassification;
use App\Models\Apartment;
use App\Models\Block;
use App\Models\Condominium;
use App\Models\Profile;
use App\Models\Resident;
use App\Models\User;
use App\Services\UserApartmentLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApartmentLinkServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserApartmentLinkService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserApartmentLinkService::class);
    }

    public function test_apartment_ids_for_user_includes_owned_units(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);
        $block = Block::factory()->create(['condominium_id' => $condominium->id]);

        $ownedApartment = Apartment::factory()->create([
            'condominium_id' => $condominium->id,
            'block_id' => $block->id,
        ]);

        $resident = Resident::factory()->proprietario()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $user->id,
        ]);
        $ownedApartment->update(['owner_id' => $resident->id]);

        $ids = $this->service->apartmentIdsForUser($user->id, $condominium->id);

        $this->assertContains($ownedApartment->id, $ids);
    }

    public function test_users_for_apartment_package_audience_excludes_non_resident_owner(): void
    {
        $condominium = Condominium::factory()->create();
        $moradorProfile = Profile::factory()->create(['name' => 'Morador']);
        $block = Block::factory()->create(['condominium_id' => $condominium->id]);

        $apartment = Apartment::factory()->create([
            'condominium_id' => $condominium->id,
            'block_id' => $block->id,
        ]);

        $ownerUser = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $moradorProfile->id,
        ]);

        $ownerResident = Resident::factory()->proprietario()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $ownerUser->id,
        ]);
        $apartment->update(['owner_id' => $ownerResident->id]);

        $tenantUser = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $moradorProfile->id,
        ]);

        $tenantResident = Resident::factory()->inquilino()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $tenantUser->id,
        ]);
        $apartment->update(['tenant_id' => $tenantResident->id]);

        $users = $this->service->usersForApartmentPackageAudience($condominium->id, $apartment->id);

        $this->assertCount(1, $users);
        $this->assertTrue($users->contains('id', $tenantUser->id));
        $this->assertFalse($users->contains('id', $ownerUser->id));
    }

    public function test_users_for_apartment_includes_morador_classification(): void
    {
        $condominium = Condominium::factory()->create();
        $moradorProfile = Profile::factory()->create(['name' => 'Morador']);
        $block = Block::factory()->create(['condominium_id' => $condominium->id]);

        $apartment = Apartment::factory()->create([
            'condominium_id' => $condominium->id,
            'block_id' => $block->id,
        ]);

        $user = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $moradorProfile->id,
        ]);

        $resident = Resident::factory()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $user->id,
            'classification' => ResidentClassification::MORADOR,
        ]);

        $apartment->residents()->attach($resident->id, ['type' => 'morador', 'active' => true]);

        $users = $this->service->usersForApartmentPackageAudience($condominium->id, $apartment->id);

        $this->assertCount(1, $users);
        $this->assertTrue($users->contains('id', $user->id));
    }
}
