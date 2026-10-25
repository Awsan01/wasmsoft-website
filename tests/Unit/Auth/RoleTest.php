<?php

namespace Tests\Unit\Auth;

use App\Auth\Permission;
use App\Auth\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    /**
     * The complete role × permission matrix. Written out by hand so a change
     * to any role's grants has to be made deliberately in both places.
     *
     * @return array<string, array{Role, Permission, bool}>
     */
    public static function matrix(): array
    {
        $expected = [
            'administrator' => [
                Permission::ViewDashboard, Permission::ViewContent, Permission::ManageContent, Permission::WritePosts,
                Permission::PublishPosts, Permission::UploadMedia, Permission::ManageMedia, Permission::ManageNavigation,
                Permission::HandleInquiries, Permission::DeleteInquiries, Permission::ManageUsers, Permission::ManageSettings,
                Permission::ViewAuditLog,
            ],
            'editor' => [
                Permission::ViewDashboard, Permission::ViewContent, Permission::ManageContent, Permission::WritePosts,
                Permission::PublishPosts, Permission::UploadMedia, Permission::ManageMedia, Permission::ManageNavigation,
                Permission::HandleInquiries,
            ],
            'author' => [Permission::ViewDashboard, Permission::ViewContent, Permission::WritePosts, Permission::UploadMedia],
            'viewer' => [Permission::ViewDashboard, Permission::ViewContent],
        ];

        $cases = [];

        foreach (Role::cases() as $role) {
            foreach (Permission::cases() as $permission) {
                $granted = in_array($permission, $expected[$role->value], true);
                $cases["{$role->value} ".($granted ? 'has' : 'lacks')." {$permission->value}"] = [$role, $permission, $granted];
            }
        }

        return $cases;
    }

    #[DataProvider('matrix')]
    public function test_role_grants_exactly_the_documented_permissions(Role $role, Permission $permission, bool $granted): void
    {
        $this->assertSame($granted, $role->grants($permission));
    }

    public function test_only_the_administrator_role_is_privileged(): void
    {
        $privileged = array_values(array_filter(Role::cases(), fn (Role $role): bool => $role->isPrivileged()));

        $this->assertSame([Role::Administrator], $privileged);
    }
}
