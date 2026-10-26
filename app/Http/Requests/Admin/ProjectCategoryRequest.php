<?php

namespace App\Http\Requests\Admin;

use App\Models\ProjectCategory;

class ProjectCategoryRequest extends TaxonomyRequest
{
    protected function model(): string
    {
        return ProjectCategory::class;
    }
}
