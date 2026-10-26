<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PostCategoryRequest;
use App\Models\PostCategory;
use Illuminate\Http\RedirectResponse;

class PostCategoryController extends TaxonomyController
{
    public function store(PostCategoryRequest $request): RedirectResponse
    {
        return $this->persist($request, new PostCategory, __('admin.content.created'));
    }

    public function update(PostCategoryRequest $request, string $term): RedirectResponse
    {
        return $this->persist($request, $this->find($term), __('admin.content.saved'));
    }

    protected function model(): string
    {
        return PostCategory::class;
    }

    protected function routes(): string
    {
        return 'admin.post-categories';
    }

    protected function texts(): string
    {
        return 'admin.post_categories';
    }

    protected function usage(): string
    {
        return 'posts';
    }

    protected function parent(): array
    {
        return ['label' => __('admin.posts.title'), 'url' => route('admin.posts.index')];
    }
}
