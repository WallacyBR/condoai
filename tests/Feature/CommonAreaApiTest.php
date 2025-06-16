<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Condominium;
use App\Models\Apartment;
use App\Models\CommonArea;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

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

    public function test_authenticated_user_can_create_common_area(): void
    {
        $token = $this->authenticate();
        $condo = Condominium::factory()->create(['active' => true]);
        $data = CommonArea::factory()->make(['condominium_id' => $condo->id, 'active' => true])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/common-areas', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_can_create_booking_and_check_conflict(): void
    {
        $token = $this->authenticate();
        $condo = Condominium::factory()->create(['active' => true]);
        $block = \App\Models\Block::factory()->create(['condominium_id' => $condo->id]);
        $apartmentId = DB::table('apartments')->insertGetId([
            'number' => '01',
            'floor' => 1,
            'block_id' => $block->id,
            'owner_id' => null,
            'tenant_id' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $apartment = Apartment::find($apartmentId);
        $area = CommonArea::factory()->create(['condominium_id' => $condo->id, 'active' => true]);

        $start = Carbon::now()->addDay();
        $booking = [
            'common_area_id' => $area->id,
            'apartment_id' => $apartment->id,
            'start_at' => $start->toDateTimeString(),
            'end_at' => $start->copy()->addHour()->toDateTimeString()
        ];

        $resp1 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/common-area-bookings', $booking);
        $resp1->assertStatus(201);

        $resp2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/common-area-bookings', $booking);
        $resp2->assertStatus(422);
    }
}
