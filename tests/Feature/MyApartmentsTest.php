<?php

namespace Tests\Feature;

use App\Enums\ResidentClassification;
use App\Models\Apartment;
use App\Models\Block;
use App\Models\Condominium;
use App\Models\Profile;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class MyApartmentsTest extends TestCase
{
    use InteractsWithTenant;
    use RefreshDatabase;

    public function test_my_apartments_returns_linked_user_units(): void
    {
        $condominium = Condominium::factory()->create(['name' => 'Residencial Primavera']);
        $moradorProfile = Profile::factory()->create(['name' => 'Morador']);

        $user = User::factory()->create([
            'condominium_id' => $condominium->id,
            'profile_id' => $moradorProfile->id,
        ]);

        $block = Block::factory()->create([
            'condominium_id' => $condominium->id,
            'name' => 'Bloco Primavera',
        ]);

        $apartment = Apartment::factory()->create([
            'condominium_id' => $condominium->id,
            'block_id' => $block->id,
            'number' => '402',
        ]);

        $resident = Resident::factory()->inquilino()->create([
            'condominium_id' => $condominium->id,
            'user_id' => $user->id,
            'apartment_id' => $apartment->id,
        ]);

        $apartment->update(['tenant_id' => $resident->id]);

        $this->actingAsTenant($user, $condominium);

        $response = $this->getJson('/api/my-apartments');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.apartment_id', $apartment->id)
            ->assertJsonPath('data.0.unit_number', '402')
            ->assertJsonPath('data.0.block_or_tower', 'Bloco Primavera')
            ->assertJsonPath('data.0.condominium_name', 'Residencial Primavera')
            ->assertJsonPath('data.0.classification', ResidentClassification::INQUILINO->value)
            ->assertJsonPath('data.0.is_default', true)
            ->assertJsonPath('data.0.resident_id', $resident->id);
    }

    public function test_my_apartments_empty_without_resident_link(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);

        $this->actingAsTenant($user, $condominium);

        $this->getJson('/api/my-apartments')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
