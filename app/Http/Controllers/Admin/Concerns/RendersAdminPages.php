<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\Brand;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders a dashboard page: a React page in resources/js/pages/admin with
 * its data, title and breadcrumbs (shared data: HandleAdminInertiaRequests).
 */
trait RendersAdminPages
{
    /**
     * @param  array<string, mixed>  $props
     * @param  list<array{label: string, url: string|null}>  $breadcrumbs  the trail; just the title when empty
     */
    protected function adminPage(string $component, string $title, array $props = [], array $breadcrumbs = []): Response
    {
        $site = __('admin.meta_title', ['site' => app(Brand::class)->name()]);

        return Inertia::render('admin/'.$component, [
            ...$props,
            'title' => $title,
            'breadcrumbs' => $breadcrumbs === [] ? [['label' => $title, 'url' => null]] : $breadcrumbs,
            'head' => ['<title data-inertia="title">'.e($title.' · '.$site).'</title>'],
        ]);
    }
}
