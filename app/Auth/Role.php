<?php

namespace App\Auth;

/**
 * Dashboard roles. Permissions are granted explicitly per role (least
 * privilege); nothing is implied by a role's position in the list.
 */
enum Role: string
{
    case Administrator = 'administrator';
    case Editor = 'editor';
    case Author = 'author';
    case Viewer = 'viewer';

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Administrator => Permission::cases(),
            self::Editor => [
                Permission::ViewDashboard,
                Permission::ViewContent,
                Permission::ManageContent,
                Permission::WritePosts,
                Permission::PublishPosts,
                Permission::UploadMedia,
                Permission::ManageMedia,
                Permission::ManageNavigation,
                Permission::HandleInquiries,
            ],
            self::Author => [
                Permission::ViewDashboard,
                Permission::ViewContent,
                Permission::WritePosts,
                Permission::UploadMedia,
            ],
            self::Viewer => [
                Permission::ViewDashboard,
                Permission::ViewContent,
            ],
        };
    }

    public function grants(Permission $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }

    /**
     * Privileged roles can only be granted or revoked by an administrator.
     */
    public function isPrivileged(): bool
    {
        return $this === self::Administrator;
    }

    public function label(): string
    {
        return __("admin.roles.{$this->value}.label");
    }

    public function description(): string
    {
        return __("admin.roles.{$this->value}.description");
    }
}
