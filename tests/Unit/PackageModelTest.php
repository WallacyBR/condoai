<?php

namespace Tests\Unit;

use App\Models\Apartment;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_salva_novos_campos_e_relacao_usuario(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\A3o suporta altera\xC3\A7\xC3\A3o de colunas');
        }
        $user = User::factory()->create();
        $apartment = Apartment::factory()->create();

        $package = Package::factory()->create([
            'user_id' => $user->id,
            'apartment_id' => $apartment->id,
        ]);

        $this->assertNotNull($package->pickup_location);
        $this->assertNotNull($package->observation);
        $this->assertEquals($user->id, $package->user->id);
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'user_id' => $user->id,
            'pickup_location' => $package->pickup_location,
        ]);
    }
}
