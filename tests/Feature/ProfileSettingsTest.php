<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileSettingsTest extends TestCase
{
    use InteractsWithTenant;
    use RefreshDatabase;

    protected Condominium $condominium;

    protected Profile $profile;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominium = Condominium::factory()->create();
        $this->profile = Profile::factory()->create([
            'name' => 'Síndico',
        ]);

        $this->user = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'profile_id' => $this->profile->id,
            'password' => Hash::make('senha-atual'),
            'notification_email' => true,
            'notification_push' => false,
            'notification_sms' => false,
        ]);
        $this->user->condominiums()->sync([$this->condominium->id]);

        $this->actingAsTenant($this->user, $this->condominium);
    }

    public function test_can_fetch_current_profile_settings(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertOk()
            ->assertJson([
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'role' => 'Síndico',
                'notifications' => [
                    'email' => true,
                    'push' => false,
                    'sms' => false,
                ],
            ]);
    }

    public function test_can_update_profile_settings(): void
    {
        $response = $this->putJson('/api/profile', [
            'name' => 'Usuário Atualizado',
            'email' => 'usuario.atualizado@example.com',
            'phone' => '(11) 98888-7777',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Usuário Atualizado',
                'email' => 'usuario.atualizado@example.com',
                'phone' => '(11) 98888-7777',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Usuário Atualizado',
            'email' => 'usuario.atualizado@example.com',
            'phone' => '(11) 98888-7777',
        ]);
    }

    public function test_can_update_password_and_notifications(): void
    {
        $token = JWTAuth::fromUser($this->user);

        $passwordResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->withHeader('X-Condominium-Id', (string) $this->condominium->id)
            ->putJson('/api/profile/password', [
                'current_password' => 'senha-atual',
                'new_password' => 'NovaSenhaSegura123',
                'new_password_confirmation' => 'NovaSenhaSegura123',
            ]);

        $passwordResponse->assertOk()
            ->assertJson([
                'message' => 'Senha atualizada com sucesso. Faça login novamente.',
            ]);

        $this->assertTrue(Hash::check('NovaSenhaSegura123', $this->user->fresh()->password));

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->withHeader('X-Condominium-Id', (string) $this->condominium->id)
            ->getJson('/api/profile')
            ->assertUnauthorized();

        $this->actingAsTenant($this->user->fresh(), $this->condominium);

        $notificationsResponse = $this->putJson('/api/profile/notifications', [
            'email' => false,
            'push' => true,
            'sms' => true,
        ]);

        $notificationsResponse->assertOk()
            ->assertJson([
                'message' => 'Preferências de notificação atualizadas com sucesso.',
                'notifications' => [
                    'email' => false,
                    'push' => true,
                    'sms' => true,
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'notification_email' => false,
            'notification_push' => true,
            'notification_sms' => true,
        ]);
    }

    public function test_password_update_rejects_weak_password(): void
    {
        $response = $this->putJson('/api/profile/password', [
            'current_password' => 'senha-atual',
            'new_password' => '123',
            'new_password_confirmation' => '123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['new_password']);
    }
}
