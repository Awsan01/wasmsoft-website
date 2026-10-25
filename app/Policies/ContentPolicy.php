<?php

namespace App\Policies;

use App\Auth\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Default rules for dashboard-managed content: every dashboard role may
 * read it, editors and administrators may change it. Model policies
 * extend this and add their own rules (system pages, post ownership…).
 */
abstract class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewContent);
    }

    public function view(User $user, Model $content): bool
    {
        return $user->hasPermission(Permission::ViewContent);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ManageContent);
    }

    public function update(User $user, Model $content): bool
    {
        return $user->hasPermission(Permission::ManageContent);
    }

    public function delete(User $user, Model $content): bool
    {
        return $user->hasPermission(Permission::ManageContent);
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermission(Permission::ManageContent);
    }
}
