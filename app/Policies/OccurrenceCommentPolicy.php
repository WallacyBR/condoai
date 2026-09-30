<?php

namespace App\Policies;

use App\Enums\ModuleName;
use App\Models\Occurrence;
use App\Models\OccurrenceComment;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantAndModule;
use Illuminate\Auth\Access\HandlesAuthorization;

class OccurrenceCommentPolicy
{
    use ChecksTenantAndModule;
    use HandlesAuthorization;

    public function create(User $user, Occurrence $occurrence): bool
    {
        if ($this->bypassesAuthorization($user)) {
            return true;
        }

        if (! $this->belongsToActiveTenant($occurrence) || ! $this->hasActiveTenantMembership($user)) {
            return false;
        }

        return $user->canModule(ModuleName::Ocorrencias->value, 'create');
    }

    public function delete(User $user, OccurrenceComment $occurrenceComment): bool
    {
        if ($this->bypassesAuthorization($user)) {
            return true;
        }

        $occurrence = $occurrenceComment->occurrence;

        if ($occurrence === null || ! $this->belongsToActiveTenant($occurrence) || ! $this->hasActiveTenantMembership($user)) {
            return false;
        }

        if ((int) $user->id === (int) $occurrenceComment->user_id) {
            return true;
        }

        return $user->canModule(ModuleName::Ocorrencias->value, 'update');
    }
}
