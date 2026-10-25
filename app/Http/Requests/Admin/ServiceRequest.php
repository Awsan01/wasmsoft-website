<?php

namespace App\Http\Requests\Admin;

use App\Content\ContentIcon;
use App\Content\PublicationStatus;
use App\Media\MediaKind;
use App\Models\Service;
use Illuminate\Validation\Rule;

class ServiceRequest extends TranslatableRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return $service instanceof Service
            ? ($this->user()?->can('update', $service) ?? false)
            : ($this->user()?->can('create', Service::class) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            ...$this->translationRules(),
            'status' => ['required', Rule::enum(PublicationStatus::class)],
            'icon' => ['nullable', Rule::enum(ContentIcon::class)],
            'image_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where('kind', MediaKind::Image->value)],
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
            'icon' => __('admin.fields.icon'),
            'image_id' => __('admin.fields.image'),
            'is_featured' => __('admin.fields.is_featured'),
        ];
    }

    public function status(): PublicationStatus
    {
        return PublicationStatus::from($this->string('status')->toString());
    }

    /**
     * @return array{icon: ?string, image_id: ?int, is_featured: bool}
     */
    public function serviceAttributes(): array
    {
        return [
            'icon' => $this->validated('icon'),
            'image_id' => $this->filled('image_id') ? $this->integer('image_id') : null,
            'is_featured' => $this->boolean('is_featured'),
        ];
    }

    protected function translationFields(): array
    {
        return [
            'title' => ['string', 'max:200'],
            'slug' => $this->slugRules(),
            'summary' => ['string', 'max:600'],
            'body' => ['string', 'max:60000'],
            'benefits' => ['array', 'max:12'],
            'benefits.*' => ['string', 'max:200'],
            'meta_title' => ['string', 'max:70'],
            'meta_description' => ['string', 'max:200'],
        ];
    }

    protected function lineFields(): array
    {
        return ['benefits'];
    }

    protected function content(): Service
    {
        $service = $this->route('service');

        return $service instanceof Service ? $service : new Service;
    }
}
