<?php

namespace App\Navigation;

use App\Auth\Permission;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Route;

/**
 * Builds the dashboard sidebar for a user.
 *
 * A module is listed only when its route exists and the user holds the
 * permission that route requires. Hiding links is a convenience: every
 * route enforces the same permission on the server.
 */
class AdminNavigation
{
    /**
     * Badge counters by route name (e.g. unread inquiries), registered by modules.
     *
     * @var array<string, Closure(User): ?int>
     */
    private array $badges = [];

    /**
     * @param  Closure(User): ?int  $counter
     */
    public function badge(string $route, Closure $counter): void
    {
        $this->badges[$route] = $counter;
    }

    /**
     * @return list<array{key: string, label: ?string, links: list<AdminLink>}>
     */
    public function groups(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        $groups = [];

        foreach ($this->definition() as $key => $items) {
            $links = [];

            foreach ($items as [$route, $activePatterns, $icon, $permission, $label]) {
                if (! Route::has($route) || ! $user->hasPermission($permission)) {
                    continue;
                }

                $links[] = new AdminLink(
                    label: __("admin.nav.{$label}"),
                    url: route($route),
                    icon: $icon,
                    active: request()->routeIs(...$activePatterns),
                    badge: isset($this->badges[$route]) ? ($this->badges[$route])($user) : null,
                );
            }

            if ($links !== []) {
                $groups[] = ['key' => $key, 'label' => $this->groupLabel($key), 'links' => $links];
            }
        }

        return $groups;
    }

    /** The first group (the overview) has no heading. */
    private function groupLabel(string $key): ?string
    {
        if ($key === 'main') {
            return null;
        }

        return __("admin.nav.groups.{$key}");
    }

    /**
     * Sidebar structure: group => [route, active route patterns, icon, required permission, label key].
     *
     * @return array<string, list<array{0: string, 1: list<string>, 2: string, 3: Permission, 4: string}>>
     */
    private function definition(): array
    {
        return [
            'main' => [
                ['admin.dashboard', ['admin.dashboard'], 'layout-dashboard', Permission::ViewDashboard, 'overview'],
            ],
            'content' => [
                ['admin.pages.index', ['admin.pages.*'], 'file-text', Permission::ViewContent, 'pages'],
                ['admin.services.index', ['admin.services.*'], 'layers', Permission::ViewContent, 'services'],
                ['admin.projects.index', ['admin.projects.*', 'admin.project-categories.*'], 'briefcase', Permission::ViewContent, 'projects'],
                ['admin.posts.index', ['admin.posts.*', 'admin.post-categories.*', 'admin.tags.*'], 'newspaper', Permission::ViewContent, 'posts'],
                ['admin.highlights.index', ['admin.highlights.*'], 'sparkles', Permission::ViewContent, 'highlights'],
                ['admin.testimonials.index', ['admin.testimonials.*'], 'quote', Permission::ViewContent, 'testimonials'],
                ['admin.team-members.index', ['admin.team-members.*'], 'users', Permission::ViewContent, 'team'],
                ['admin.milestones.index', ['admin.milestones.*'], 'flag', Permission::ViewContent, 'milestones'],
            ],
            'library' => [
                ['admin.media.index', ['admin.media.*'], 'image', Permission::ViewContent, 'media'],
            ],
            'inbox' => [
                ['admin.inquiries.index', ['admin.inquiries.*'], 'inbox', Permission::HandleInquiries, 'inquiries'],
            ],
            'site' => [
                ['admin.navigation.index', ['admin.navigation.*'], 'list', Permission::ManageNavigation, 'navigation'],
                ['admin.settings.edit', ['admin.settings.*'], 'settings', Permission::ManageSettings, 'settings'],
            ],
            'administration' => [
                ['admin.users.index', ['admin.users.*'], 'user-plus', Permission::ManageUsers, 'users'],
                ['admin.audit.index', ['admin.audit.*'], 'shield-check', Permission::ViewAuditLog, 'audit'],
            ],
        ];
    }
}
