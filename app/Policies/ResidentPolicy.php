<?php

namespace App\Policies;

use App\Enums\ModuleName;
use App\Models\Resident;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndModule;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResidentPolicy
{
    use ChecksTenantAndModule;
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($this->bypassesAuthorization($user)) {
            return true;
        }

        return $this->canAccessModule($user, ModuleName::Moradores->value, 'view');
    }

    public function view(User $user, Resident $resident): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $resident,
            ModuleName::Moradores->value,
            'view'
        );
    }

    public function create(User $user): bool
    {
        if ($this->bypassesAuthorization($user)) {
            return true;
        }

        return $this->canAccessModule($user, ModuleName::Moradores->value, 'create');
    }

    public function update(User $user, Resident $resident): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $resident,
            ModuleName::Moradores->value,
            'update'
        );
    }

    public function delete(User $user, Resident $resident): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $resident,
            ModuleName::Moradores->value,
            'delete'
        );
    }

    public function linkUser(User $user, Resident $resident): bool
    {
        return $this->update($user, $resident);
    }
}
