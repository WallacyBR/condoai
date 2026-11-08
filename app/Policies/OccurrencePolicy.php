<?php

namespace App\Policies;

use App\Enums\ModuleName;
use App\Models\Occurrence;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndModule;
use Illuminate\Auth\Access\HandlesAuthorization;

class OccurrencePolicy
{
    use ChecksTenantAndModule;
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($this->bypassesAuthorization($user)) {
            return true;
        }

        return $this->canAccessModule($user, ModuleName::Ocorrencias->value, 'view');
    }

    public function view(User $user, Occurrence $occurrence): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $occurrence,
            ModuleName::Ocorrencias->value,
            'view',
            true,
            $occurrence->user_id
        );
    }

    public function create(User $user): bool
    {
        if ($this->bypassesAuthorization($user)) {
            return true;
        }

        return $this->canAccessModule($user, ModuleName::Ocorrencias->value, 'create');
    }

    public function update(User $user, Occurrence $occurrence): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $occurrence,
            ModuleName::Ocorrencias->value,
            'update',
            true,
            $occurrence->user_id
        );
    }

    public function delete(User $user, Occurrence $occurrence): bool
    {
        return $this->canAccessTenantResource(
            $user,
            $occurrence,
            ModuleName::Ocorrencias->value,
            'delete',
            true,
            $occurrence->user_id
        );
    }

    public function restore(User $user, Occurrence $occurrence): bool
    {
        return $this->delete($user, $occurrence);
    }

    public function forceDelete(User $user, Occurrence $occurrence): bool
    {
        return $this->delete($user, $occurrence);
    }
}
