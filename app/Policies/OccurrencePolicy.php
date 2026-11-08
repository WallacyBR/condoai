<?php

namespace App\Policies;

use App\Models\Occurrence;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OccurrencePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view_occurrences') || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Occurrence  $occurrence
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Occurrence $occurrence)
    {
        if ($user->isSuperAdmin()) return true;
        // Users can view occurrences in their own condominium
        // Or if they have global permission to view occurrences
        return ($user->hasPermissionTo('view_occurrences') && $user->condominium_id === $occurrence->condominium_id)
            || $user->id === $occurrence->user_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create_occurrences') || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Occurrence  $occurrence
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Occurrence $occurrence)
    {
        if ($user->isSuperAdmin()) return true;
        // Users can update occurrences they created or if they have permission to update any occurrence in their condominium
        return ($user->hasPermissionTo('edit_occurrences') && $user->condominium_id === $occurrence->condominium_id)
            || $user->id === $occurrence->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Occurrence  $occurrence
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Occurrence $occurrence)
    {
         if ($user->isSuperAdmin()) return true;
        // Users can delete occurrences they created or if they have permission to delete any occurrence in their condominium
        return ($user->hasPermissionTo('delete_occurrences') && $user->condominium_id === $occurrence->condominium_id)
            || $user->id === $occurrence->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Occurrence  $occurrence
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Occurrence $occurrence)
    {
        return $user->hasPermissionTo('delete_occurrences') || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Occurrence  $occurrence
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Occurrence $occurrence)
    {
        return $user->hasPermissionTo('delete_occurrences') || $user->isSuperAdmin();
    }
}
