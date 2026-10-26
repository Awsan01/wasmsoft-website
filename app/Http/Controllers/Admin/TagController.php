<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;

class TagController extends TaxonomyController
{
    public function store(TagRequest $request): RedirectResponse
    {
        return $this->persist($request, new Tag, __('admin.content.created'));
    }

    public function update(TagRequest $request, string $term): RedirectResponse
    {
        return $this->persist($request, $this->find($term), __('admin.content.saved'));
    }

    protected function model(): string
    {
        return Tag::class;
    }

    protected function routes(): string
    {
        return 'admin.tags';
    }

    protected function texts(): string
    {
        return 'admin.tags';
    }

    protected function usage(): string
    {
        return 'posts';
    }

    protected function parent(): array
    {
        return ['label' => __('admin.posts.title'), 'url' => route('admin.posts.index')];
    }

    protected function sortable(): bool
    {
        return false;
    }
}
