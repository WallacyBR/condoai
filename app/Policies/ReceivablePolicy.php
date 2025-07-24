<?php

namespace App\Policies;

use App\Models\Receivable;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReceivablePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view_receivables') || $user->isSuperAdmin();
    }

    public function view(User $user, Receivable $receivable)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->condominium_id === $receivable->condominium_id &&
            $user->hasPermissionTo('view_receivables');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create_receivables') || $user->isSuperAdmin();
    }

    public function approve(User $user, Receivable $receivable)
    {
        return $user->hasPermissionTo('approve_receivables') || $user->isSuperAdmin();
    }

    public function reconcile(User $user, Receivable $receivable)
    {
        return $user->hasPermissionTo('reconcile_receivables') || $user->isSuperAdmin();
    }
}
