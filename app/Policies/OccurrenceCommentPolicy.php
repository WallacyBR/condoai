<?php

namespace App\Policies;

use App\Models\OccurrenceComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OccurrenceCommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Any authenticated user associated with the occurrence's condominium can comment
        // (assuming the occurrence itself is accessible to them, checked in controller)
        return $user->exists;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\OccurrenceComment  $occurrenceComment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, OccurrenceComment $occurrenceComment)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        // User can delete their own comment or if they have permission to manage occurrences
        return $user->id === $occurrenceComment->user_id ||
               ($user->hasPermissionTo('edit_occurrences') && $user->condominium_id === $occurrenceComment->occurrence->condominium_id);
    }
}
