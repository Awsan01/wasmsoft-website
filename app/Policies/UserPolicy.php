<?php

namespace App\Policies;

use App\Auth\Permission;
use App\Models\User;

/**
 * Dashboard accounts are managed by administrators only (they alone can
 * grant roles). Nobody deletes their own account; keeping at least one
 * active administrator is checked where roles and status change.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ManageUsers);
    }

    public function view(User $user, User $account): bool
    {
        return $user->hasPermission(Permission::ManageUsers);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ManageUsers);
    }

    public function update(User $user, User $account): bool
    {
        return $user->hasPermission(Permission::ManageUsers);
    }

    public function delete(User $user, User $account): bool
    {
        return $user->hasPermission(Permission::ManageUsers) && ! $user->is($account);
    }
}
