<?php

namespace App\Policies;

use App\Enums\ModuleName;
use App\Models\ExpenseRequest;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndModule;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpenseRequestPolicy
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

    public function view(User $user, ExpenseRequest $expenseRequest): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $expenseRequest,
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

    public function update(User $user, ExpenseRequest $expenseRequest): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $expenseRequest,
            ModuleName::Financeiro->value,
            'update'
        );
    }

    public function delete(User $user, ExpenseRequest $expenseRequest): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $expenseRequest,
            ModuleName::Financeiro->value,
            'delete'
        );
    }

    public function approve(User $user, ExpenseRequest $expenseRequest): bool
    {
        if (! $this->update($user, $expenseRequest)) {
            return false;
        }

        if ((int) $user->id === (int) $expenseRequest->requested_by) {
            return false;
        }

        return true;
    }
}
