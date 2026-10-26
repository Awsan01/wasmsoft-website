<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ProjectCategoryRequest;
use App\Models\ProjectCategory;
use Illuminate\Http\RedirectResponse;

class ProjectCategoryController extends TaxonomyController
{
    public function store(ProjectCategoryRequest $request): RedirectResponse
    {
        return $this->persist($request, new ProjectCategory, __('admin.content.created'));
    }

    public function update(ProjectCategoryRequest $request, string $term): RedirectResponse
    {
        return $this->persist($request, $this->find($term), __('admin.content.saved'));
    }

    protected function model(): string
    {
        return ProjectCategory::class;
    }

    protected function routes(): string
    {
        return 'admin.project-categories';
    }

    protected function texts(): string
    {
        return 'admin.project_categories';
    }

    protected function usage(): string
    {
        return 'projects';
    }

    protected function parent(): array
    {
        return ['label' => __('admin.projects.title'), 'url' => route('admin.projects.index')];
    }
}
