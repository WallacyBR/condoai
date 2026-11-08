<?php

namespace App\Policies;

use App\Enums\ModuleName;
use App\Models\ChartAccount;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndModule;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChartAccountPolicy
{
    use ChecksTenantAndModule;
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($this->bypassesAuthorization($user)) {
            return true;
        }

        return $this->canAccessModule($user, ModuleName::Financeiro->value, 'view');
    }

    public function view(User $user, ChartAccount $chartAccount): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $chartAccount,
            ModuleName::Financeiro->value,
            'view'
        );
    }

    public function create(User $user): bool
    {
        if ($this->bypassesAuthorization($user)) {
            return true;
        }

        return $this->canAccessModule($user, ModuleName::Financeiro->value, 'create');
    }

    public function update(User $user, ChartAccount $chartAccount): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $chartAccount,
            ModuleName::Financeiro->value,
            'update'
        );
    }

    public function delete(User $user, ChartAccount $chartAccount): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $chartAccount,
            ModuleName::Financeiro->value,
            'delete'
        );
    }
}
