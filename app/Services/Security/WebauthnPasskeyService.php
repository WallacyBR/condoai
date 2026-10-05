<?php

namespace App\Services\Security;

use App\Models\User;
use App\Models\UserPasskey;
use App\Models\WebauthnChallenge;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\CredentialRecord;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class WebauthnPasskeyService
{
    private SerializerInterface $serializer;

    private AttestationStatementSupportManager $attestationStatementSupportManager;

    public function __construct()
    {
        $this->attestationStatementSupportManager = new AttestationStatementSupportManager([
            new NoneAttestationStatementSupport,
        ]);
        $this->serializer = (new WebauthnSerializerFactory($this->attestationStatementSupportManager))->create();
    }

    public function registrationOptions(Request $request, User $user, ?string $name = null): array
    {
        $challenge = random_bytes(32);
        $options = PublicKeyCredentialCreationOptions::create(
            PublicKeyCredentialRpEntity::create(config('app.name', 'PrediHub'), $this->publicRpId($request)),
            PublicKeyCredentialUserEntity::create($user->email, $this->userHandle($user), $user->name),
            $challenge,
            [
                PublicKeyCredentialParameters::createPk(-7),
                PublicKeyCredentialParameters::createPk(-257),
            ],
            AuthenticatorSelectionCriteria::create(
                null,
                AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED
            ),
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            $this->credentialDescriptors($user->passkeys()->active()->get())
        );

        return $this->storeChallenge($user, 'registration', $options, $name);
    }

    public function loginOptions(Request $request, ?string $email = null): array
    {
        $user = $email ? User::where('email', $email)->first() : null;
        $challenge = random_bytes(32);
        $options = PublicKeyCredentialRequestOptions::create(
            $challenge,
            $this->publicRpId($request),
            $user ? $this->credentialDescriptors($user->passkeys()->active()->get()) : [],
            PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED
        );

        return $this->storeChallenge($user, 'login', $options, null, $email);
    }

    public function verifyRegistration(Request $request, string $token, array $credentialPayload, ?string $name = null): UserPasskey
    {
        $challenge = $this->consumeChallenge($token, 'registration');
        $user = $challenge->user;
        abort_if(! $user, 422, 'Desafio de registro inválido.');

        $credential = $this->credentialFromPayload($credentialPayload);
        abort_if(! $credential->response instanceof AuthenticatorAttestationResponse, 422, 'Resposta de registro inválida.');

        $options = $this->optionsFromChallenge($challenge, PublicKeyCredentialCreationOptions::class);
        $record = AuthenticatorAttestationResponseValidator::create($this->ceremonyFactory($request)->creationCeremony())
            ->check($credential->response, $options, $this->rpId($request));

        $encodedCredentialId = $this->base64Url($record->publicKeyCredentialId);
        $passkey = UserPasskey::updateOrCreate(
            ['credential_id_hash' => $this->credentialHash($encodedCredentialId)],
            [
                'user_id' => $user->id,
                'condominium_id' => $user->condominium_id,
                'name' => $name ?: $challenge->options['friendly_name'] ?? 'Passkey',
                'credential_id' => $encodedCredentialId,
                'credential_public_key' => $this->base64Url($record->credentialPublicKey),
                'credential_record' => $this->normalize($record),
                'user_handle' => $this->base64Url($record->userHandle),
                'transports' => $record->transports,
                'sign_count' => $record->counter,
                'backup_eligible' => $record->backupEligible,
                'backup_status' => $record->backupStatus,
                'revoked_at' => null,
                'revoked_by' => null,
            ]
        );

        $challenge->markUsed();

        return $passkey->fresh(['user']);
    }

    public function verifyLogin(Request $request, string $token, array $credentialPayload): User
    {
        $challenge = $this->consumeChallenge($token, 'login');
        $credential = $this->credentialFromPayload($credentialPayload);
        abort_if(! $credential->response instanceof AuthenticatorAssertionResponse, 422, 'Resposta de autenticação inválida.');

        $encodedCredentialId = $this->base64Url($credential->rawId);
        $passkey = UserPasskey::active()
            ->with('user.profile.modules', 'user.condominium')
            ->where('credential_id_hash', $this->credentialHash($encodedCredentialId))
            ->first();

        abort_if(! $passkey, 422, 'Passkey não encontrada ou revogada.');
        abort_if($challenge->email && $passkey->user?->email !== $challenge->email, 422, 'Passkey não pertence ao usuário informado.');

        $record = $this->denormalize($passkey->credential_record, CredentialRecord::class);
        $options = $this->optionsFromChallenge($challenge, PublicKeyCredentialRequestOptions::class);
        $updatedRecord = AuthenticatorAssertionResponseValidator::create($this->ceremonyFactory($request)->requestCeremony())
            ->check(
                $record,
                $credential->response,
                $options,
                $this->rpId($request),
                $challenge->user ? $this->userHandle($challenge->user) : null
            );

        $passkey->forceFill([
            'credential_record' => $this->normalize($updatedRecord),
            'sign_count' => $updatedRecord->counter,
            'backup_eligible' => $updatedRecord->backupEligible,
            'backup_status' => $updatedRecord->backupStatus,
            'last_used_at' => now(),
        ])->save();

        $challenge->markUsed();

        return $passkey->user->load(['profile.modules', 'condominium']);
    }

    public function credentialHash(string $credentialId): string
    {
        return hash('sha256', $credentialId);
    }

    private function storeChallenge(
        ?User $user,
        string $type,
        PublicKeyCredentialCreationOptions|PublicKeyCredentialRequestOptions $options,
        ?string $friendlyName = null,
        ?string $email = null
    ): array {
        $normalizedOptions = $this->browserSafeOptions($this->normalize($options));
        if ($friendlyName) {
            $normalizedOptions['friendly_name'] = $friendlyName;
        }

        $challenge = WebauthnChallenge::create([
            'token' => (string) Str::uuid(),
            'user_id' => $user?->id,
            'condominium_id' => $user?->condominium_id,
            'email' => $email ?? $user?->email,
            'type' => $type,
            'challenge' => $normalizedOptions['challenge'],
            'options' => $normalizedOptions,
            'expires_at' => now()->addMinutes(5),
        ]);

        unset($normalizedOptions['friendly_name']);

        return [
            'token' => $challenge->token,
            'publicKey' => $normalizedOptions,
        ];
    }

    private function consumeChallenge(string $token, string $type): WebauthnChallenge
    {
        $challenge = WebauthnChallenge::with('user')->where('token', $token)->where('type', $type)->first();
        abort_if(! $challenge, 422, 'Desafio não encontrado.');
        abort_if($challenge->used_at !== null, 422, 'Desafio já utilizado.');
        abort_if($challenge->expires_at->isPast(), 422, 'Desafio expirado.');

        return $challenge;
    }

    private function browserSafeOptions(array $options): array
    {
        if (($options['rp']['id'] ?? null) === null) {
            unset($options['rp']['id']);
        }

        if (($options['rpId'] ?? null) === null) {
            unset($options['rpId']);
        }

        return $options;
    }

    /**
     * @return PublicKeyCredentialDescriptor[]
     */
    private function credentialDescriptors($passkeys): array
    {
        return $passkeys->map(fn (UserPasskey $passkey) => PublicKeyCredentialDescriptor::create(
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            Base64UrlSafe::decodeNoPadding($passkey->credential_id),
            $passkey->transports ?? []
        ))->values()->all();
    }

    private function ceremonyFactory(Request $request): CeremonyStepManagerFactory
    {
        $factory = new CeremonyStepManagerFactory;
        $factory->setAllowedOrigins([$request->getSchemeAndHttpHost()]);
        $factory->setSecuredRelyingPartyId(['localhost', '127.0.0.1', '::1']);

        return $factory;
    }

    private function credentialFromPayload(array $payload): PublicKeyCredential
    {
        return $this->denormalize($payload, PublicKeyCredential::class);
    }

    private function optionsFromChallenge(WebauthnChallenge $challenge, string $class): mixed
    {
        $options = $challenge->options;
        unset($options['friendly_name']);

        return $this->denormalize($options, $class);
    }

    private function normalize(mixed $value): array
    {
        /** @var array<string, mixed> $normalized */
        $normalized = $this->serializer->normalize($value, 'json');

        return $normalized;
    }

    private function denormalize(array $value, string $class): mixed
    {
        return $this->serializer->denormalize($value, $class, 'json');
    }

    private function rpId(Request $request): string
    {
        return $request->getHost();
    }

    private function publicRpId(Request $request): ?string
    {
        $host = $request->getHost();

        return filter_var($host, FILTER_VALIDATE_IP) ? null : $host;
    }

    private function userHandle(User $user): string
    {
        return 'condoai-user-'.$user->id;
    }

    private function base64Url(string $value): string
    {
        return Base64UrlSafe::encodeUnpadded($value);
    }
}
