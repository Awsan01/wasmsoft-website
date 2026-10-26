<?php

namespace App\Http\Requests\Admin;

use App\Models\Tag;

class TagRequest extends TaxonomyRequest
{
    protected function model(): string
    {
        return Tag::class;
    }
}
