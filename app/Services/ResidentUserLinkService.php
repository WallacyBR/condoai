<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Orquestra vínculo/desvínculo staff User ↔ Resident com membership e perfil Morador.
 */
class ResidentUserLinkService
{
    public function __construct(
        private readonly UserApartmentLinkService $apartmentLinkService
    ) {
    }

    public function link(Resident $resident, ?int $userId, int $condominiumId): Resident
    {
        if ((int) $resident->condominium_id !== $condominiumId) {
            throw new InvalidArgumentException('Morador não pertence ao tenant ativo.');
        }

        if ($userId === null) {
            $resident->update(['user_id' => null]);

            return $resident->fresh();
        }

        $user = User::query()->findOrFail($userId);

        if ($user->hasStaffProfile()) {
            throw new InvalidArgumentException('Usuários com perfil staff não podem ser vinculados como morador.');
        }

        $existing = Resident::withoutGlobalScopes()
            ->where('condominium_id', $condominiumId)
            ->where('user_id', $userId)
            ->where('id', '!=', $resident->id)
            ->exists();

        if ($existing) {
            throw new InvalidArgumentException('Este usuário já está vinculado a outro morador neste condomínio.');
        }

        return DB::transaction(function () use ($resident, $user, $condominiumId) {
            $this->ensureMembershipAndMoradorProfile($user, $condominiumId);

            $resident->update(['user_id' => $user->id]);

            return $resident->fresh(['user', 'condominium']);
        });
    }

    private function ensureMembershipAndMoradorProfile(User $user, int $condominiumId): void
    {
        if (! $user->belongsToCondominium($condominiumId)) {
            $user->condominiums()->syncWithoutDetaching([$condominiumId]);
        }

        $moradorProfile = Profile::query()->where('name', 'Morador')->first();

        if ($moradorProfile !== null && ! $user->hasStaffProfile()) {
            $user->update(['profile_id' => $moradorProfile->id]);
        }
    }
}
