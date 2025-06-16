<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): string
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret'),
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        return $login['authorization']['token'];
    }

    public function test_residents_are_isolated_by_domain(): void
    {
        $token = $this->authenticate();

        $alpha = Condominium::factory()->create(['domain' => 'alpha']);
        $beta = Condominium::factory()->create(['domain' => 'beta']);

        Resident::factory()->count(2)->create(['condominium_id' => $alpha->id]);
        Resident::factory()->count(3)->create(['condominium_id' => $beta->id]);

        app()->instance('currentCondominium', $alpha);
        $responseAlpha = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/residents');

        $responseAlpha->assertStatus(200)->assertJsonCount(2);

        app()->instance('currentCondominium', $beta);
        $responseBeta = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/residents');

        $responseBeta->assertStatus(200)->assertJsonCount(3);
    }
}
