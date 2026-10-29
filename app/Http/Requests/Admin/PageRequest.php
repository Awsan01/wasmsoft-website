<?php

namespace App\Http\Requests\Admin;

use App\Content\PageTemplate;
use App\Content\PublicationStatus;
use App\Media\MediaKind;
use App\Models\Page;
use Illuminate\Validation\Rule;

/**
 * Create or update a page. System pages have no slug, template, status or
 * picture: their address is fixed and they are always published.
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
            $rules['image_id'] = ['nullable', 'integer', Rule::exists('media', 'id')->where('kind', MediaKind::Image->value)];
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
            'image_id' => __('admin.pages.image'),
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
        if ($this->content()->isSystem()) {
            return [];
        }

        return [
            'template' => $this->validated('template'),
            'image_id' => $this->filled('image_id') ? $this->integer('image_id') : null,
        ];
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
