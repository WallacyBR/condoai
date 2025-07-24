<?php

namespace App\Policies;

use App\Models\Payable;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayablePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view_payables') || $user->isSuperAdmin();
    }

    public function view(User $user, Payable $payable)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->condominium_id === $payable->condominium_id &&
            $user->hasPermissionTo('view_payables');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_payables') || $user->isSuperAdmin();
    }

    public function approve(User $user, Payable $payable)
    {
        return $user->hasPermissionTo('approve_payables') || $user->isSuperAdmin();
    }

    public function reconcile(User $user, Payable $payable)
    {
        return $user->hasPermissionTo('reconcile_payables') || $user->isSuperAdmin();
    }
}
