<?php

namespace App\Http\Requests\Admin;

use App\Content\PageTemplate;
use App\Content\PublicationStatus;
use App\Models\Page;
use Illuminate\Validation\Rule;

/**
 * Create or update a page. System pages have no slug, template or status:
 * their address is fixed and they are always published.
 */
class PageRequest extends TranslatableRequest
{
    public function authorize(): bool
    {
        $page = $this->route('page');

        return $page instanceof Page
            ? ($this->user()?->can('update', $page) ?? false)
            : ($this->user()?->can('create', Page::class) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $rules = $this->translationRules();

        if (! $this->content()->isSystem()) {
            $rules['template'] = ['required', Rule::enum(PageTemplate::class)];
            $rules['status'] = ['required', Rule::enum(PublicationStatus::class)];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'template' => __('admin.fields.template'),
            'status' => __('admin.fields.status'),
        ];
    }

    public function status(): ?PublicationStatus
    {
        return $this->content()->isSystem() ? null : PublicationStatus::from($this->string('status')->toString());
    }

    /**
     * @return array<string, mixed>
     */
    public function pageAttributes(): array
    {
        return $this->content()->isSystem() ? [] : ['template' => $this->validated('template')];
    }

    protected function translationFields(): array
    {
        return [
            'title' => ['string', 'max:200'],
            ...($this->content()->isSystem() ? [] : ['slug' => $this->slugRules()]),
            'summary' => ['string', 'max:600'],
            'body' => ['string', 'max:60000'],
            'meta_title' => ['string', 'max:70'],
            'meta_description' => ['string', 'max:200'],
        ];
    }

    protected function content(): Page
    {
        $page = $this->route('page');

        return $page instanceof Page ? $page : new Page;
    }

    /**
     * First path segments of the fixed public routes.
     *
     * @return list<string>
     */
    protected function reservedSlugs(): array
    {
        return ['services', 'projects', 'news', 'contact'];
    }
}
