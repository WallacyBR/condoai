<?php

namespace App\Policies;

use App\Enums\ModuleName;
use App\Models\Payable;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndModule;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayablePolicy
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

    public function view(User $user, Payable $payable): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $payable,
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

    public function update(User $user, Payable $payable): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $payable,
            ModuleName::Financeiro->value,
            'update'
        );
    }

    public function delete(User $user, Payable $payable): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $payable,
            ModuleName::Financeiro->value,
            'delete'
        );
    }

    public function approve(User $user, Payable $payable): bool
    {
        return $this->update($user, $payable);
    }

    public function reconcile(User $user, Payable $payable): bool
    {
        return $this->update($user, $payable);
    }
}
