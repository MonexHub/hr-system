<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OrganizationUnit;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationUnitPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_organization::unit');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrganizationUnit $organizationUnit): bool
    {
        return $user->can('view_organization::unit');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_organization::unit');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrganizationUnit $organizationUnit): bool
    {
        return $user->can('update_organization::unit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrganizationUnit $organizationUnit): bool
    {
        return $user->can('delete_organization::unit');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_organization::unit');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, OrganizationUnit $organizationUnit): bool
    {
        return $user->can('force_delete_organization::unit');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_organization::unit');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, OrganizationUnit $organizationUnit): bool
    {
        return $user->can('restore_organization::unit');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_organization::unit');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, OrganizationUnit $organizationUnit): bool
    {
        return $user->can('replicate_organization::unit');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_organization::unit');
    }
}
