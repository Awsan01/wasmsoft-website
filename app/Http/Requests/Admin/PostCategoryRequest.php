<?php

namespace App\Http\Requests\Admin;

use App\Models\PostCategory;

class PostCategoryRequest extends TaxonomyRequest
{
    protected function model(): string
    {
        return PostCategory::class;
    }
}
