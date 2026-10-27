<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesLocalizedText;
use App\Models\Milestone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MilestoneRequest extends FormRequest
{
    use ValidatesLocalizedText;

    public function authorize(): bool
    {
        $milestone = $this->route('block');

        return is_string($milestone)
            ? ($this->user()?->can('update', Milestone::query()->findOrFail($milestone)) ?? false)
            : ($this->user()?->can('create', Milestone::class) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'between:1900,'.(now()->year + 1)],
            ...$this->localizedRules('title', 120),
            ...$this->localizedRules('description', 400),
            'is_visible' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'year' => __('admin.milestones.fields.year'),
            ...$this->localizedAttributes('title', __('admin.fields.title')),
            ...$this->localizedAttributes('description', __('admin.milestones.fields.description')),
            'is_visible' => __('admin.blocks.visible_label'),
        ];
    }

    /**
     * @return list<\Closure(Validator): void>
     */
    public function after(): array
    {
        return [fn (Validator $validator) => $this->requireInSomeLanguage($validator, 'title')];
    }

    /**
     * @return array<string, mixed>
     */
    public function milestoneAttributes(): array
    {
        return [
            'year' => $this->integer('year'),
            'title' => $this->localizedText('title'),
            'description' => $this->localizedText('description'),
            'is_visible' => $this->boolean('is_visible'),
        ];
    }
}
