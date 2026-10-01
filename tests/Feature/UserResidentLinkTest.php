<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Block;
use App\Models\Condominium;
use App\Models\Profile;
use App\Models\Resident;
use App\Models\User;
use Database\Seeders\DefaultProfileSeeder;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AssignsModulePermissions;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class UserResidentLinkTest extends TestCase
{
    use AssignsModulePermissions;
    use InteractsWithTenant;
    use RefreshDatabase;

    private Condominium $condominiumA;

    private Condominium $condominiumB;

    private User $staffUser;

    private Profile $moradorProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ModuleSeeder::class);
        $this->seed(DefaultProfileSeeder::class);

        $this->condominiumA = Condominium::factory()->create(['name' => 'Condomínio Alpha']);
        $this->condominiumB = Condominium::factory()->create(['name' => 'Condomínio Beta']);

        $sindicoProfile = Profile::query()->where('name', 'Síndico')->firstOrFail();
        $this->assignModulePermissions($sindicoProfile, 'Moradores', [
            'can_view' => true,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => true,
            'scope' => 'condominium',
        ]);

        $this->staffUser = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $sindicoProfile->id,
        ]);

        $this->moradorProfile = Profile::query()->where('name', 'Morador')->firstOrFail();
    }

    public function test_staff_can_link_user_to_resident(): void
    {
        $resident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
        ]);

        $targetUser = User::factory()->create([
            'email' => 'morador.link@teste.com',
            'profile_id' => $this->moradorProfile->id,
        ]);

        $this->actingAsTenant($this->staffUser, $this->condominiumA);

        $response = $this->putJson("/api/residents/{$resident->id}/link-user", [
            'user_id' => $targetUser->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('user_id', $targetUser->id);

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'user_id' => $targetUser->id,
        ]);
    }

    public function test_link_creates_membership_and_morador_profile_when_missing(): void
    {
        $resident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
        ]);

        $targetUser = User::factory()->create([
            'condominium_id' => $this->condominiumB->id,
            'profile_id' => null,
        ]);

        $this->assertFalse($targetUser->belongsToCondominium($this->condominiumA->id));
        $this->assertDatabaseMissing('condominium_user', [
            'user_id' => $targetUser->id,
            'condominium_id' => $this->condominiumA->id,
        ]);

        $this->actingAsTenant($this->staffUser, $this->condominiumA);

        $response = $this->putJson("/api/residents/{$resident->id}/link-user", [
            'user_id' => $targetUser->id,
        ]);

        $response->assertOk();

        $targetUser->refresh();
        $this->assertTrue($targetUser->belongsToCondominium($this->condominiumA->id));
        $this->assertDatabaseHas('condominium_user', [
            'user_id' => $targetUser->id,
            'condominium_id' => $this->condominiumA->id,
        ]);
        $this->assertSame('Morador', $targetUser->profile?->name);
    }

    public function test_staff_profile_cannot_be_linked_as_resident(): void
    {
        $resident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
        ]);

        $zeladorProfile = Profile::query()->where('name', 'Zelador')->firstOrFail();
        $staffTarget = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $zeladorProfile->id,
        ]);

        $this->actingAsTenant($this->staffUser, $this->condominiumA);

        $response = $this->putJson("/api/residents/{$resident->id}/link-user", [
            'user_id' => $staffTarget->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Usuários com perfil staff não podem ser vinculados como morador.']);
    }

    public function test_unique_user_per_condominium_on_link(): void
    {
        $existingResident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
        ]);

        $anotherResident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
        ]);

        $targetUser = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->moradorProfile->id,
        ]);

        $this->actingAsTenant($this->staffUser, $this->condominiumA);

        $this->putJson("/api/residents/{$existingResident->id}/link-user", [
            'user_id' => $targetUser->id,
        ])->assertOk();

        $response = $this->putJson("/api/residents/{$anotherResident->id}/link-user", [
            'user_id' => $targetUser->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Este usuário já está vinculado a outro morador neste condomínio.']);
    }

    public function test_cross_tenant_link_is_denied(): void
    {
        $residentB = Resident::factory()->create([
            'condominium_id' => $this->condominiumB->id,
        ]);

        $targetUser = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->moradorProfile->id,
        ]);

        $this->actingAsTenant($this->staffUser, $this->condominiumA);

        $response = $this->putJson("/api/residents/{$residentB->id}/link-user", [
            'user_id' => $targetUser->id,
        ]);

        $response->assertNotFound();
    }

    public function test_unlink_user_from_resident(): void
    {
        $targetUser = User::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'profile_id' => $this->moradorProfile->id,
        ]);

        $resident = Resident::factory()->create([
            'condominium_id' => $this->condominiumA->id,
            'user_id' => $targetUser->id,
        ]);

        $this->actingAsTenant($this->staffUser, $this->condominiumA);

        $response = $this->putJson("/api/residents/{$resident->id}/link-user", [
            'user_id' => null,
        ]);

        $response->assertOk()
            ->assertJsonPath('user_id', null);

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'user_id' => null,
        ]);
    }
}
