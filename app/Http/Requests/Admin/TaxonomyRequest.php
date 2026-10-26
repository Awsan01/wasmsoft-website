<?php

namespace App\Http\Requests\Admin;

use App\Content\TranslatableContent;
use Illuminate\Database\Eloquent\Model;

/**
 * Create or update a vocabulary term (category or tag): a translated name
 * with a per-language slug. The route parameter is {term}.
 */
abstract class TaxonomyRequest extends TranslatableRequest
{
    private (Model&TranslatableContent)|null $term = null;

    /**
     * @return class-string<Model&TranslatableContent>
     */
    abstract protected function model(): string;

    public function authorize(): bool
    {
        $term = $this->content();

        return $term->exists
            ? ($this->user()?->can('update', $term) ?? false)
            : ($this->user()?->can('create', $this->model()) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->translationRules();
    }

    protected function translationFields(): array
    {
        return [
            'name' => ['string', 'max:100'],
            'slug' => $this->slugRules(),
        ];
    }

    protected function requiredTranslationFields(): array
    {
        return ['name'];
    }

    protected function content(): Model&TranslatableContent
    {
        if ($this->term === null) {
            $id = $this->route('term');
            $model = $this->model();

            $this->term = is_string($id) ? $model::query()->findOrFail($id) : new $model;
        }

        return $this->term;
    }
}
