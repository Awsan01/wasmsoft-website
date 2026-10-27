<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesLocalizedText;
use App\Media\MediaKind;
use App\Models\Testimonial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TestimonialRequest extends FormRequest
{
    use ValidatesLocalizedText;

    public function authorize(): bool
    {
        $testimonial = $this->route('block');

        return is_string($testimonial)
            ? ($this->user()?->can('update', Testimonial::query()->findOrFail($testimonial)) ?? false)
            : ($this->user()?->can('create', Testimonial::class) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            ...$this->localizedRules('quote', 600),
            'author_name' => ['required', 'string', 'max:120'],
            ...$this->localizedRules('author_role', 120),
            'company' => ['nullable', 'string', 'max:150'],
            'photo_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where('kind', MediaKind::Image->value)],
            'has_consent' => ['boolean'],
            'is_visible' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...$this->localizedAttributes('quote', __('admin.testimonials.fields.quote')),
            'author_name' => __('admin.testimonials.fields.author_name'),
            ...$this->localizedAttributes('author_role', __('admin.testimonials.fields.author_role')),
            'company' => __('admin.testimonials.fields.company'),
            'photo_id' => __('admin.testimonials.fields.photo'),
            'has_consent' => __('admin.testimonials.fields.has_consent'),
            'is_visible' => __('admin.blocks.visible_label'),
        ];
    }

    /**
     * @return list<\Closure(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $this->requireInSomeLanguage($validator, 'quote');

            // A quote without the person's consent is never published.
            if ($this->boolean('is_visible') && ! $this->boolean('has_consent')) {
                $validator->errors()->add('has_consent', __('admin.testimonials.consent_required'));
            }
        }];
    }

    /**
     * @return array<string, mixed>
     */
    public function testimonialAttributes(): array
    {
        return [
            'quote' => $this->localizedText('quote'),
            'author_name' => $this->string('author_name')->trim()->toString(),
            'author_role' => $this->localizedText('author_role'),
            'company' => $this->filled('company') ? $this->string('company')->trim()->toString() : null,
            'photo_id' => $this->filled('photo_id') ? $this->integer('photo_id') : null,
            'has_consent' => $this->boolean('has_consent'),
            'is_visible' => $this->boolean('is_visible'),
        ];
    }
}
