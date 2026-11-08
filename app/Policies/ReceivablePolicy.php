<?php

namespace App\Policies;

use App\Enums\ModuleName;
use App\Models\Receivable;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndModule;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReceivablePolicy
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

    public function view(User $user, Receivable $receivable): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $receivable,
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

    public function update(User $user, Receivable $receivable): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $receivable,
            ModuleName::Financeiro->value,
            'update'
        );
    }

    public function delete(User $user, Receivable $receivable): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $receivable,
            ModuleName::Financeiro->value,
            'delete'
        );
    }

    public function approve(User $user, Receivable $receivable): bool
    {
        return $this->update($user, $receivable);
    }

    public function reconcile(User $user, Receivable $receivable): bool
    {
        return $this->update($user, $receivable);
    }
}
