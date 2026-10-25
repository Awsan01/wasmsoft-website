<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * System pages (home, listing introductions) can be edited but never deleted.
 */
class PagePolicy extends ContentPolicy
{
    public function delete(User $user, Model $content): bool
    {
        return parent::delete($user, $content) && $content instanceof Page && ! $content->isSystem();
    }
}
