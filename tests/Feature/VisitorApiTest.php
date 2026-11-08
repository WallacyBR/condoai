<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visitor;
use App\Models\Apartment;
use App\Models\Condominium;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorApiTest extends TestCase
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

    public function test_authenticated_user_can_create_visitor(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\xA3o suporta altera\xC3\xA7\xC3\xB5es de colunas');
        }
        $token = $this->authenticate();
        $condo = Condominium::factory()->create();
        $apartment = Apartment::factory()->create(['condominium_id' => $condo->id]);
        $data = Visitor::factory()->make([
            'condominium_id' => $condo->id,
            'apartment_id' => $apartment->id
        ])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/visitors', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_authenticated_user_can_view_visitor(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\xA3o suporta altera\xC3\xA7\xC3\xB5es de colunas');
        }
        $token = $this->authenticate();
        $visitor = Visitor::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/visitors/' . $visitor->id);

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $visitor->id]);
    }
}
