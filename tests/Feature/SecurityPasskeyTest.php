<?php

namespace Tests\Feature;

use App\Models\AccessLog;
use App\Models\Condominium;
use App\Models\Module;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserPasskey;
use App\Models\WebauthnChallenge;
use App\Services\Security\WebauthnPasskeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class SecurityPasskeyTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    private Condominium $condominium;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominium = Condominium::factory()->create();
        $this->user = User::factory()->create(['condominium_id' => $this->condominium->id]);
    }

    public function test_user_registers_and_revokes_own_passkey(): void
    {
        $this->actingAsTenant($this->user, $this->condominium);

        $passkey = $this->makePasskey($this->user, ['name' => 'MacBook']);
        $service = Mockery::mock(WebauthnPasskeyService::class);
        $service->shouldReceive('registrationOptions')->once()->andReturn([
            'token' => 'token-teste',
            'publicKey' => ['challenge' => 'abc', 'rp' => ['name' => 'CondoAI'], 'user' => ['id' => 'abc']],
        ]);
        $service->shouldReceive('verifyRegistration')->once()->andReturn($passkey);
        $this->app->instance(WebauthnPasskeyService::class, $service);

        $this->postJson('/api/security/passkeys/register/options', ['name' => 'MacBook'])
            ->assertOk()
            ->assertJsonPath('token', 'token-teste');

        $this->postJson('/api/security/passkeys/register/verify', [
            'token' => 'token-teste',
            'name' => 'MacBook',
            'credential' => ['id' => 'cred'],
        ])->assertCreated()->assertJsonPath('name', 'MacBook');

        $this->deleteJson("/api/security/passkeys/{$passkey->id}")->assertNoContent();

        $this->assertNotNull($passkey->fresh()->revoked_at);
        $this->assertDatabaseHas('access_logs', [
            'user_id' => $this->user->id,
            'method' => 'passkey',
            'status' => 'success',
        ]);
    }

    public function test_passkey_login_endpoint_issues_jwt(): void
    {
        $service = Mockery::mock(WebauthnPasskeyService::class);
        $service->shouldReceive('loginOptions')->once()->with(Mockery::type('Illuminate\Http\Request'), $this->user->email)->andReturn([
            'token' => 'login-token',
            'publicKey' => ['challenge' => 'abc', 'rpId' => 'localhost', 'allowCredentials' => []],
        ]);
        $service->shouldReceive('verifyLogin')->once()->andReturn($this->user->load(['profile.modules', 'condominium']));
        $this->app->instance(WebauthnPasskeyService::class, $service);

        $this->postJson('/api/auth/passkeys/login/options', ['email' => $this->user->email])
            ->assertOk()
            ->assertJsonPath('token', 'login-token');

        $this->postJson('/api/auth/passkeys/login/verify', [
            'token' => 'login-token',
            'credential' => ['id' => 'cred'],
        ])->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['authorization' => ['token', 'type']]);
    }

    public function test_revoked_passkey_is_not_listed_as_active_for_login_options(): void
    {
        $this->actingAs($this->user, 'api');
        $active = $this->makePasskey($this->user, ['credential_id' => $this->base64Url('active-credential')]);
        $this->makePasskey($this->user, ['credential_id' => $this->base64Url('revoked-credential'), 'revoked_at' => now()]);

        $service = app(WebauthnPasskeyService::class);
        $options = $service->loginOptions(request(), $this->user->email);

        $credentialIds = collect($options['publicKey']['allowCredentials'])->pluck('id');

        $this->assertTrue($credentialIds->contains($active->credential_id));
        $this->assertFalse($credentialIds->contains('revoked-credential'));
    }

    public function test_local_ip_options_do_not_expose_invalid_rp_id_to_browser(): void
    {
        $request = Request::create('http://127.0.0.1:8000/security/passkeys/register/options', 'POST');
        $service = app(WebauthnPasskeyService::class);

        $registrationOptions = $service->registrationOptions($request, $this->user, 'MacBook');
        $loginOptions = $service->loginOptions($request, $this->user->email);

        $this->assertArrayNotHasKey('id', $registrationOptions['publicKey']['rp']);
        $this->assertArrayNotHasKey('rpId', $loginOptions['publicKey']);
    }

    public function test_expired_challenge_is_rejected_before_verification(): void
    {
        $challenge = WebauthnChallenge::create([
            'token' => 'expired-token',
            'user_id' => $this->user->id,
            'condominium_id' => $this->condominium->id,
            'email' => $this->user->email,
            'type' => 'login',
            'challenge' => 'abc',
            'options' => ['challenge' => 'abc'],
            'expires_at' => now()->subMinute(),
        ]);

        $this->postJson('/api/auth/passkeys/login/verify', [
            'token' => $challenge->token,
            'credential' => ['id' => 'cred'],
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Desafio expirado.');
    }

    public function test_admin_access_logs_and_passkey_revocation_are_tenant_aware(): void
    {
        $admin = $this->makeSecurityAdmin();
        $otherCondominium = Condominium::factory()->create();
        $otherUser = User::factory()->create(['condominium_id' => $otherCondominium->id]);
        $sameTenantPasskey = $this->makePasskey($this->user);
        $otherPasskey = $this->makePasskey($otherUser);

        AccessLog::create([
            'user_id' => $this->user->id,
            'condominium_id' => $this->condominium->id,
            'email' => $this->user->email,
            'method' => 'password',
            'status' => 'success',
            'occurred_at' => now(),
        ]);
        AccessLog::create([
            'user_id' => $otherUser->id,
            'condominium_id' => $otherCondominium->id,
            'email' => $otherUser->email,
            'method' => 'password',
            'status' => 'success',
            'occurred_at' => now(),
        ]);

        $this->actingAsTenant($admin, $this->condominium);

        $this->getJson('/api/security/access-logs')
            ->assertOk()
            ->assertJsonFragment(['email' => $this->user->email])
            ->assertJsonMissing(['email' => $otherUser->email]);

        $this->deleteJson("/api/security/users/{$otherUser->id}/passkeys/{$otherPasskey->id}")
            ->assertNotFound();

        $this->deleteJson("/api/security/users/{$this->user->id}/passkeys/{$sameTenantPasskey->id}")
            ->assertNoContent();

        $this->assertNotNull($sameTenantPasskey->fresh()->revoked_at);
    }

    public function test_password_login_and_logout_are_logged(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ])->assertOk();

        $this->assertDatabaseHas('access_logs', [
            'user_id' => $this->user->id,
            'method' => 'password',
            'status' => 'success',
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/auth/logout')
            ->assertOk();

        $this->assertDatabaseHas('access_logs', [
            'user_id' => $this->user->id,
            'method' => 'logout',
            'status' => 'success',
        ]);
    }

    private function makeSecurityAdmin(): User
    {
        $profile = Profile::factory()->create(['name' => 'Síndico']);
        $module = Module::factory()->create(['name' => 'Segurança']);
        $profile->modules()->attach($module->id, [
            'can_view' => true,
            'can_create' => false,
            'can_update' => true,
            'can_delete' => true,
            'scope' => 'own',
        ]);

        return User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'profile_id' => $profile->id,
        ]);
    }

    private function makePasskey(User $user, array $attributes = []): UserPasskey
    {
        $credentialId = $attributes['credential_id'] ?? $this->base64Url('credential-'.$user->id.'-'.uniqid());

        return UserPasskey::create(array_merge([
            'user_id' => $user->id,
            'condominium_id' => $user->condominium_id,
            'name' => 'Passkey de Teste',
            'credential_id' => $credentialId,
            'credential_id_hash' => hash('sha256', $credentialId),
            'credential_public_key' => 'public-key',
            'credential_record' => [
                'publicKeyCredentialId' => $credentialId,
                'type' => 'public-key',
                'transports' => [],
                'attestationType' => 'none',
                'trustPath' => [],
                'aaguid' => '00000000-0000-0000-0000-000000000000',
                'credentialPublicKey' => 'public-key',
                'userHandle' => 'user-handle',
                'counter' => 0,
            ],
            'user_handle' => 'user-handle',
            'transports' => [],
            'sign_count' => 0,
        ], $attributes));
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
