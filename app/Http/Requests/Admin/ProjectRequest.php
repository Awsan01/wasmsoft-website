<?php

namespace App\Http\Requests\Admin;

use App\Content\PublicationStatus;
use App\Media\MediaKind;
use App\Models\Project;
use Illuminate\Validation\Rule;

class ProjectRequest extends TranslatableRequest
{
    public const MAX_GALLERY_IMAGES = 24;

    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            ? ($this->user()?->can('update', $project) ?? false)
            : ($this->user()?->can('create', Project::class) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $image = Rule::exists('media', 'id')->where('kind', MediaKind::Image->value);

        return [
            ...$this->translationRules(),
            'status' => ['required', Rule::enum(PublicationStatus::class)],
            'project_category_id' => ['nullable', 'integer', Rule::exists('project_categories', 'id')],
            'cover_id' => ['nullable', 'integer', $image],
            'gallery' => ['nullable', 'array', 'max:'.self::MAX_GALLERY_IMAGES],
            'gallery.*' => ['integer', 'distinct', $image],
            'client_name' => ['nullable', 'string', 'max:150'],
            'project_url' => ['nullable', 'string', 'max:255', 'url:https,http'],
            'technologies' => ['nullable', 'array', 'max:20'],
            'technologies.*' => ['string', 'max:40', 'distinct:ignore_case'],
            'completed_on' => ['nullable', 'date_format:Y-m-d'],
            'is_featured' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'status' => __('admin.fields.status'),
            'project_category_id' => __('admin.fields.category'),
            'cover_id' => __('admin.fields.cover'),
            'gallery' => __('admin.fields.gallery'),
            'gallery.*' => __('admin.fields.gallery'),
            'client_name' => __('admin.fields.client_name'),
            'project_url' => __('admin.fields.project_url'),
            'technologies' => __('admin.fields.technologies'),
            'technologies.*' => __('admin.fields.technologies'),
            'completed_on' => __('admin.fields.completed_on'),
            'is_featured' => __('admin.fields.is_featured'),
        ];
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Technologies are typed as one comma-separated line.
        if (is_string($technologies = $this->input('technologies'))) {
            $names = array_map(trim(...), preg_split('/[,\n]/u', $technologies) ?: []);
            $this->merge(['technologies' => array_values(array_filter($names, fn (string $name): bool => $name !== ''))]);
        }
    }

    public function status(): PublicationStatus
    {
        return PublicationStatus::from($this->string('status')->toString());
    }

    /**
     * @return array<string, mixed>
     */
    public function projectAttributes(): array
    {
        $technologies = $this->validated('technologies');

        return [
            'project_category_id' => $this->filled('project_category_id') ? $this->integer('project_category_id') : null,
            'cover_id' => $this->filled('cover_id') ? $this->integer('cover_id') : null,
            'client_name' => $this->validated('client_name'),
            'project_url' => $this->validated('project_url'),
            'technologies' => is_array($technologies) && $technologies !== [] ? $technologies : null,
            'completed_on' => $this->validated('completed_on'),
            'is_featured' => $this->boolean('is_featured'),
        ];
    }

    /**
     * Gallery media ids in display order.
     *
     * @return list<int>
     */
    public function galleryIds(): array
    {
        $ids = $this->validated('gallery');

        return is_array($ids) ? array_values(array_map(intval(...), $ids)) : [];
    }

    protected function translationFields(): array
    {
        return [
            'title' => ['string', 'max:200'],
            'slug' => $this->slugRules(),
            'summary' => ['string', 'max:600'],
            'body' => ['string', 'max:60000'],
            'meta_title' => ['string', 'max:70'],
            'meta_description' => ['string', 'max:200'],
        ];
    }

    protected function content(): Project
    {
        $project = $this->route('project');

        return $project instanceof Project ? $project : new Project;
    }
}
