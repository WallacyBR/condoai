<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksTenantAndModule
{
    protected function bypassesAuthorization(User $user): bool
    {
        return $user->isPlatformAdmin();
    }

    protected function hasActiveTenantMembership(User $user): bool
    {
        $tenantId = tenant()?->id;

        if ($tenantId === null) {
            return false;
        }

        return $user->belongsToCondominium($tenantId);
    }

    protected function belongsToActiveTenant(object $resource): bool
    {
        $tenantId = tenant()?->id;

        if ($tenantId === null) {
            return false;
        }

        $resourceCondominiumId = $resource->condominium_id ?? null;

        if ($resourceCondominiumId === null) {
            return false;
        }

        return (int) $resourceCondominiumId === $tenantId;
    }

    protected function canAccessModule(User $user, string $moduleName, string $ability): bool
    {
        if (! $this->hasActiveTenantMembership($user)) {
            return false;
        }

        return $user->canModule($moduleName, $ability);
    }

    protected function canAccessTenantResource(
        User $user,
        object $resource,
        string $moduleName,
        string $ability,
        bool $allowOwnScopeOnlyForAuthor = false,
        ?int $authorId = null
    ): bool {
        if ($this->bypassesAuthorization($user)) {
            return true;
        }

        if (! $this->belongsToActiveTenant($resource) || ! $this->hasActiveTenantMembership($user)) {
            return false;
        }

        if ($authorId !== null && (int) $user->id === (int) $authorId) {
            return true;
        }

        if (! $user->canModule($moduleName, $ability)) {
            return false;
        }

        if ($allowOwnScopeOnlyForAuthor && $user->moduleScope($moduleName) === 'own') {
            return false;
        }

        return true;
    }
}
