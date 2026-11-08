<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\CommonArea;
use App\Models\Condominium;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommonAreaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): string
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);
        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        return $login['authorization']['token'];
    }

    public function test_create_common_area(): void
    {
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $data = CommonArea::factory()->make([
            'condominium_id' => $condo->id,
        ])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/common-areas', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_book_common_area(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite não suporta alteração de colunas');
        }
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $area = CommonArea::factory()->create(['condominium_id' => $condo->id]);
        $apartment = Apartment::factory()->create(['condominium_id' => $condo->id]);
        $payload = [
            'apartment_id' => $apartment->id,
            'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/common-areas/'.$area->id.'/bookings', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['apartment_id' => $apartment->id]);
    }
}
