<?php

namespace App\Services\Notifications;

use App\Models\Apartment;
use App\Models\Notice;
use App\Models\Resident;
use App\Models\User;
use App\Services\UserApartmentLinkService;
use Illuminate\Support\Collection;

/**
 * Resolve audiências de notificação fail-closed por condomínio explícito (ADR-002 E1).
 */
class AudienceResolver
{
    public function __construct(
        private readonly UserApartmentLinkService $apartmentLinkService,
    ) {}

    /**
     * @param  array<int>  $userIds
     * @return Collection<int, User>
     */
    public function usersByIds(int $condoId, array $userIds): Collection
    {
        if ($userIds === []) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->where(fn ($query) => $this->applyMembershipConstraint($query, $condoId))
            ->get();
    }

    /**
     * @param  array<int, string>  $profileNames
     * @return Collection<int, User>
     */
    public function usersByProfiles(int $condoId, array $profileNames): Collection
    {
        if ($profileNames === []) {
            return collect();
        }

        return User::query()
            ->whereHas('profile', fn ($query) => $query->whereIn('name', $profileNames))
            ->where(fn ($query) => $this->applyMembershipConstraint($query, $condoId))
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function usersWithModule(int $condoId, string $moduleName, string $ability = 'view'): Collection
    {
        return User::query()
            ->where(fn ($query) => $this->applyMembershipConstraint($query, $condoId))
            ->get()
            ->filter(fn (User $user) => $user->canModule($moduleName, $ability))
            ->values();
    }

    /**
     * @return Collection<int, User>
     */
    public function singleUser(int $condoId, int $userId): Collection
    {
        $user = User::query()
            ->where('id', $userId)
            ->where(fn ($query) => $this->applyMembershipConstraint($query, $condoId))
            ->first();

        return $user !== null ? collect([$user]) : collect();
    }

    /**
     * Todos os usuários com membership no condomínio (Q3 ready).
     *
     * @return Collection<int, User>
     */
    public function usersByMembership(int $condoId): Collection
    {
        return User::query()
            ->where(fn ($query) => $this->applyMembershipConstraint($query, $condoId))
            ->get();
    }

    /**
     * Todos os papéis com user_id vinculados à unidade (#96), incluindo proprietário.
     *
     * @return Collection<int, User>
     */
    public function usersForApartment(int $condoId, int $apartmentId): Collection
    {
        $apartment = Apartment::withoutGlobalScopes()
            ->where('id', $apartmentId)
            ->where('condominium_id', $condoId)
            ->first();

        if ($apartment === null) {
            return collect();
        }

        $userIds = collect();

        if ($apartment->owner_id !== null) {
            $owner = Resident::withoutGlobalScopes()->find($apartment->owner_id);
            if ($owner?->user_id !== null) {
                $userIds->push($owner->user_id);
            }
        }

        if ($apartment->tenant_id !== null) {
            $tenant = Resident::withoutGlobalScopes()->find($apartment->tenant_id);
            if ($tenant?->user_id !== null) {
                $userIds->push($tenant->user_id);
            }
        }

        $pivotResidents = $apartment->residents()
            ->withoutGlobalScopes()
            ->wherePivot('active', true)
            ->get();

        foreach ($pivotResidents as $resident) {
            if ($resident->user_id !== null) {
                $userIds->push($resident->user_id);
            }
        }

        $directResidents = Resident::withoutGlobalScopes()
            ->where('condominium_id', $condoId)
            ->where('apartment_id', $apartmentId)
            ->whereNotNull('user_id')
            ->pluck('user_id');

        $userIds = $userIds->merge($directResidents)->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        return $this->usersByIds($condoId, $userIds->all());
    }

    /**
     * Audiência V4 de encomendas — delega ao UserApartmentLinkService (E3 ready).
     *
     * @return Collection<int, User>
     */
    public function usersForApartmentPackageAudience(int $condoId, int $apartmentId): Collection
    {
        return $this->apartmentLinkService->usersForApartmentPackageAudience($condoId, $apartmentId);
    }

    /**
     * Audiência de aviso publicado — send_to_all (Q3) ou targets morph expandidos via Q1.
     *
     * @return Collection<int, User>
     */
    public function usersForNotice(int $condoId, Notice $notice): Collection
    {
        if ($notice->send_to_all) {
            return $this->usersByMembership($condoId);
        }

        $users = collect();

        $apartmentIds = $notice->apartments()->withoutGlobalScopes()->pluck('apartments.id');
        foreach ($apartmentIds as $apartmentId) {
            $users = $users->merge($this->usersForApartment($condoId, (int) $apartmentId));
        }

        $blockIds = $notice->blocks()->withoutGlobalScopes()->pluck('blocks.id');
        foreach ($blockIds as $blockId) {
            $relatedApartmentIds = Apartment::withoutGlobalScopes()
                ->where('condominium_id', $condoId)
                ->where('block_id', $blockId)
                ->pluck('id');

            foreach ($relatedApartmentIds as $apartmentId) {
                $users = $users->merge($this->usersForApartment($condoId, (int) $apartmentId));
            }
        }

        $towerIds = $notice->towers()->withoutGlobalScopes()->pluck('towers.id');
        foreach ($towerIds as $towerId) {
            $relatedApartmentIds = Apartment::withoutGlobalScopes()
                ->where('condominium_id', $condoId)
                ->where('tower_id', $towerId)
                ->pluck('id');

            foreach ($relatedApartmentIds as $apartmentId) {
                $users = $users->merge($this->usersForApartment($condoId, (int) $apartmentId));
            }
        }

        return $users->unique('id')->values();
    }

    private function applyMembershipConstraint($query, int $condoId): void
    {
        $query->where(function ($membershipQuery) use ($condoId) {
            $membershipQuery
                ->where('condominium_id', $condoId)
                ->orWhereHas('condominiums', fn ($condoQuery) => $condoQuery->where('condominiums.id', $condoId));
        });
    }
}
