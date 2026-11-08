<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Block;
use App\Models\Condominium;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class PackageControllerTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    private User $user;

    private Condominium $condominium;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominium = Condominium::factory()->create();
        $this->user = User::factory()->create(['condominium_id' => $this->condominium->id]);

        $this->actingAsTenant($this->user, $this->condominium);
    }

    public function test_can_create_package_with_current_form_fields(): void
    {
        $apartment = $this->createApartment($this->condominium, ['number' => '101']);

        $response = $this->postJson('/api/packages', [
            'apartment_id' => $apartment->id,
            'pickup_location' => 'Portaria A',
            'observation' => 'Caixa pequena',
        ]);

        $response->assertCreated()
            ->assertJsonFragment([
                'apartment_id' => $apartment->id,
                'condominium_id' => $this->condominium->id,
                'recipient_apartment' => '101',
                'sender' => 'Remetente não informado',
            ]);

        $this->assertDatabaseHas('packages', [
            'apartment_id' => $apartment->id,
            'condominium_id' => $this->condominium->id,
            'recipient_apartment' => '101',
            'sender' => 'Remetente não informado',
        ]);
    }

    public function test_cannot_show_package_from_another_condominium(): void
    {
        $otherCondominium = Condominium::factory()->create();
        $otherApartment = $this->createApartment($otherCondominium, ['number' => '202']);
        $package = $this->createPackage($otherApartment);

        $this->getJson("/api/packages/{$package->id}")
            ->assertNotFound();
    }

    public function test_cannot_move_package_to_apartment_from_another_condominium(): void
    {
        $apartment = $this->createApartment($this->condominium, ['number' => '101']);
        $otherCondominium = Condominium::factory()->create();
        $otherApartment = $this->createApartment($otherCondominium, ['number' => '202']);
        $package = $this->createPackage($apartment);

        $this->putJson("/api/packages/{$package->id}", [
            'apartment_id' => $otherApartment->id,
            'pickup_location' => 'Portaria B',
        ])->assertUnprocessable();
    }

    public function test_cannot_withdraw_package_twice(): void
    {
        $apartment = $this->createApartment($this->condominium, ['number' => '101']);
        $package = $this->createPackage($apartment, ['code' => 'ABCD1234']);

        $this->postJson("/api/packages/{$package->id}/withdraw", [
            'code' => 'ABCD1234',
        ])->assertOk();

        $this->postJson("/api/packages/{$package->id}/withdraw", [
            'code' => 'ABCD1234',
        ])->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Encomenda já retirada']);
    }

    private function createApartment(Condominium $condominium, array $attributes = []): Apartment
    {
        $block = Block::factory()->create(['condominium_id' => $condominium->id]);

        return Apartment::factory()->create(array_merge([
            'condominium_id' => $condominium->id,
            'block_id' => $block->id,
            'owner_id' => null,
            'tenant_id' => null,
        ], $attributes));
    }

    private function createPackage(Apartment $apartment, array $attributes = []): Package
    {
        return Package::factory()->create(array_merge([
            'apartment_id' => $apartment->id,
            'condominium_id' => $apartment->condominium_id,
            'recipient_name' => 'Destinatário Teste',
            'recipient_apartment' => $apartment->number,
            'sender' => 'Remetente Teste',
            'user_id' => $this->user->id,
        ], $attributes));
    }
}
