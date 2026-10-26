<?php

namespace App\Policies;

use App\Auth\Permission;
use App\Content\PublicationStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Editors and administrators manage every post. Authors write posts and
 * may change or delete their own posts while they are drafts; once a post
 * is published, scheduled or archived, only editors can change it.
 */
class PostPolicy extends ContentPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::WritePosts);
    }

    public function update(User $user, Model $content): bool
    {
        return $user->hasPermission(Permission::PublishPosts) || $this->isOwnDraft($user, $content);
    }

    public function delete(User $user, Model $content): bool
    {
        return $user->hasPermission(Permission::PublishPosts) || $this->isOwnDraft($user, $content);
    }

    /**
     * Choose the status and publication date, feature posts and name the author.
     */
    public function publish(User $user): bool
    {
        return $user->hasPermission(Permission::PublishPosts);
    }

    private function isOwnDraft(User $user, Model $content): bool
    {
        return $content instanceof Post
            && $user->hasPermission(Permission::WritePosts)
            && $content->isWrittenBy($user)
            && $content->status === PublicationStatus::Draft;
    }
}
