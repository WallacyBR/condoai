<?php

namespace App\Policies;

use App\Enums\ModuleName;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestAttachment;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndModule;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpenseRequestAttachmentPolicy
{
    use ChecksTenantAndModule;
    use HandlesAuthorization;

    public function viewAny(User $user, ExpenseRequest $expenseRequest): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $expenseRequest,
            ModuleName::Financeiro->value,
            'view'
        );
    }

    public function view(User $user, ExpenseRequestAttachment $attachment): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $attachment,
            ModuleName::Financeiro->value,
            'view'
        );
    }

    public function create(User $user, ExpenseRequest $expenseRequest): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $expenseRequest,
            ModuleName::Financeiro->value,
            'update'
        );
    }

    public function delete(User $user, ExpenseRequestAttachment $attachment): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $attachment,
            ModuleName::Financeiro->value,
            'delete'
        );
    }
}
