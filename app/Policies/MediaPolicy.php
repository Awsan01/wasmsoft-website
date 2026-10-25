<?php

namespace App\Policies;

use App\Auth\Permission;
use App\Models\Media;
use App\Models\User;

/**
 * Every dashboard role can browse the library; uploading needs UploadMedia;
 * authors may only change or delete their own uploads, editors any file.
 */
class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewContent);
    }

    public function view(User $user, Media $media): bool
    {
        return $user->hasPermission(Permission::ViewContent);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::UploadMedia);
    }

    public function update(User $user, Media $media): bool
    {
        if ($user->hasPermission(Permission::ManageMedia)) {
            return true;
        }

        return $user->hasPermission(Permission::UploadMedia) && $media->uploaded_by === $user->id;
    }

    public function delete(User $user, Media $media): bool
    {
        return $this->update($user, $media);
    }
}
