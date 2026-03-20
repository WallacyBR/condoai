<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
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

        $this->actingAs($this->user, 'api');
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
        $passwordResponse = $this->putJson('/api/profile/password', [
            'current_password' => 'senha-atual',
            'new_password' => 'nova-senha-segura',
            'new_password_confirmation' => 'nova-senha-segura',
        ]);

        $passwordResponse->assertOk()
            ->assertJson([
                'message' => 'Senha atualizada com sucesso.',
            ]);

        $this->assertTrue(Hash::check('nova-senha-segura', $this->user->fresh()->password));

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
}
