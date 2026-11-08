<?php

namespace App\Services;

use App\Enums\ResidentClassification;
use App\Models\Apartment;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Fonte de verdade para resolução User ↔ unidade/morador (ADR-002 Q1).
 */
class UserApartmentLinkService
{
    /**
     * Apartamentos que o usuário pode representar no condomínio (assembleias: morador + proprietário).
     *
     * @return array<int>
     */
    public function apartmentIdsForUser(int $userId, int $condominiumId): array
    {
        $resident = $this->findLinkedResident($userId, $condominiumId);

        if ($resident === null) {
            return [];
        }

        return $this->apartmentIdsForResident($resident);
    }

    /**
     * Verifica se o usuário pode votar/agir na unidade informada (fail-closed cross-tenant).
     */
    public function userCanActOnApartment(int $userId, int $condominiumId, int $apartmentId): bool
    {
        $apartment = Apartment::withoutGlobalScopes()
            ->where('id', $apartmentId)
            ->where('condominium_id', $condominiumId)
            ->first();

        if ($apartment === null) {
            return false;
        }

        return in_array($apartmentId, $this->apartmentIdsForUser($userId, $condominiumId), true);
    }

    /**
     * Usuários vinculados ao apartamento para audiência de encomenda/aviso (V4: inquilino/morador only).
     *
     * @return Collection<int, User>
     */
    public function usersForApartmentPackageAudience(int $condominiumId, int $apartmentId): Collection
    {
        $apartment = Apartment::withoutGlobalScopes()
            ->where('id', $apartmentId)
            ->where('condominium_id', $condominiumId)
            ->first();

        if ($apartment === null) {
            return collect();
        }

        $userIds = collect();

        if ($apartment->tenant_id !== null) {
            $tenant = Resident::withoutGlobalScopes()->find($apartment->tenant_id);
            if ($tenant !== null && $this->isPackageAudienceResident($tenant)) {
                $userIds->push($tenant->user_id);
            }
        }

        $pivotResidents = $apartment->residents()
            ->withoutGlobalScopes()
            ->wherePivot('active', true)
            ->get();

        foreach ($pivotResidents as $resident) {
            if ($this->isPackageAudienceResident($resident, $resident->pivot->type ?? null)) {
                $userIds->push($resident->user_id);
            }
        }

        $directResidents = Resident::withoutGlobalScopes()
            ->where('condominium_id', $condominiumId)
            ->where('apartment_id', $apartmentId)
            ->get();

        foreach ($directResidents as $resident) {
            if ($this->isPackageAudienceResident($resident)) {
                $userIds->push($resident->user_id);
            }
        }

        $userIds = $userIds->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->whereHas('condominiums', fn ($query) => $query->where('condominiums.id', $condominiumId))
            ->get();
    }

    /**
     * Lista unidades formatadas para o app (GET /my-apartments).
     *
     * @return array<int, array<string, mixed>>
     */
    public function myApartmentsForUser(User $user, int $condominiumId): array
    {
        $resident = $this->findLinkedResident($user->id, $condominiumId);

        if ($resident === null) {
            return [];
        }

        $apartmentIds = $this->apartmentIdsForResident($resident);

        if ($apartmentIds === []) {
            return [];
        }

        $apartments = Apartment::withoutGlobalScopes()
            ->with(['block', 'tower', 'condominium'])
            ->whereIn('id', $apartmentIds)
            ->where('condominium_id', $condominiumId)
            ->get();

        $defaultApartmentId = $resident->apartment_id ?? $apartments->first()?->id;

        return $apartments->map(function (Apartment $apartment) use ($resident, $defaultApartmentId, $condominiumId) {
            return [
                'apartment_id' => $apartment->id,
                'unit_number' => $apartment->number,
                'block_or_tower' => $apartment->block?->name ?? $apartment->tower?->name,
                'condominium_name' => $apartment->condominium?->name,
                'classification' => $this->classificationForApartment($resident, $apartment),
                'is_default' => $defaultApartmentId !== null && (int) $apartment->id === (int) $defaultApartmentId,
                'resident_id' => $resident->id,
                'condominium_id' => $condominiumId,
            ];
        })->values()->all();
    }

    public function findLinkedResident(int $userId, int $condominiumId): ?Resident
    {
        return Resident::withoutGlobalScopes()
            ->where('condominium_id', $condominiumId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * @return array<int>
     */
    private function apartmentIdsForResident(Resident $resident): array
    {
        $ids = collect();

        if ($resident->apartment_id !== null) {
            $ids->push($resident->apartment_id);
        }

        $ids = $ids
            ->merge($resident->ownedApartments()->withoutGlobalScopes()->pluck('id'))
            ->merge($resident->rentedApartments()->withoutGlobalScopes()->pluck('id'))
            ->merge(
                $resident->apartments()
                    ->withoutGlobalScopes()
                    ->wherePivot('active', true)
                    ->pluck('apartments.id')
            );

        return $ids->unique()->filter()->values()->all();
    }

    private function isPackageAudienceResident(Resident $resident, ?string $pivotType = null): bool
    {
        if ($resident->user_id === null) {
            return false;
        }

        if (in_array($resident->classification, [
            ResidentClassification::MORADOR,
            ResidentClassification::INQUILINO,
        ], true)) {
            return true;
        }

        if ($pivotType === null) {
            return false;
        }

        $audiencePivotTypes = ['tenant', 'morador', 'inquilino', 'resident'];

        return in_array(strtolower($pivotType), $audiencePivotTypes, true);
    }

    private function classificationForApartment(Resident $resident, Apartment $apartment): string
    {
        if ((int) $apartment->tenant_id === (int) $resident->id) {
            return ResidentClassification::INQUILINO->value;
        }

        if ((int) $apartment->owner_id === (int) $resident->id) {
            return $resident->isMorador() || $resident->apartment_id === $apartment->id
                ? ResidentClassification::MORADOR->value
                : ResidentClassification::PROPRIETARIO->value;
        }

        return $resident->classification?->value ?? ResidentClassification::MORADOR->value;
    }
}
