<?php

namespace App\Policies;

use App\Auth\Permission;
use App\Models\MenuItem;
use App\Models\User;

/**
 * The site menus are managed by users with the navigation permission
 * (editors and administrators); there is nothing to read for others.
 */
class MenuItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ManageNavigation);
    }

    public function view(User $user, MenuItem $item): bool
    {
        return $user->hasPermission(Permission::ManageNavigation);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ManageNavigation);
    }

    public function update(User $user, MenuItem $item): bool
    {
        return $user->hasPermission(Permission::ManageNavigation);
    }

    public function delete(User $user, MenuItem $item): bool
    {
        return $user->hasPermission(Permission::ManageNavigation);
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermission(Permission::ManageNavigation);
    }
}
