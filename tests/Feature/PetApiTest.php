<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Apartment;
use App\Models\Pet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetApiTest extends TestCase
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

    public function test_authenticated_user_can_create_pet(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\xA3o suporta altera\xC3\xA7\xC3\xB5es de colunas');
        }
        $token = $this->authenticate();
        $apartment = Apartment::factory()->create();
        $data = Pet::factory()->make([ 'apartment_id' => $apartment->id ])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/pets', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => $data['name']]);
    }

    public function test_authenticated_user_can_update_pet(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\xA3o suporta altera\xC3\xA7\xC3\xB5es de colunas');
        }
        $token = $this->authenticate();
        $pet = Pet::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/pets/' . $pet->id, ['name' => 'Rex']);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Rex']);
    }

    public function test_authenticated_user_can_delete_pet(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite n\xC3\xA3o suporta altera\xC3\xA7\xC3\xB5es de colunas');
        }
        $token = $this->authenticate();
        $pet = Pet::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/pets/' . $pet->id);

        $response->assertStatus(204);
    }
}
