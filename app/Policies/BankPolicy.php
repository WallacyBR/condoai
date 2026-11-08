<?php

namespace App\Policies;

use App\Enums\ModuleName;
use App\Models\Bank;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndModule;
use Illuminate\Auth\Access\HandlesAuthorization;

class BankPolicy
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

    public function view(User $user, Bank $bank): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $bank,
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

    public function update(User $user, Bank $bank): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $bank,
            ModuleName::Financeiro->value,
            'update'
        );
    }

    public function delete(User $user, Bank $bank): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $bank,
            ModuleName::Financeiro->value,
            'delete'
        );
    }
}
