<?php

namespace App\Http\Requests\Admin;

use App\Content\ContentIcon;
use App\Content\HighlightGroup;
use App\Http\Requests\Admin\Concerns\ValidatesLocalizedText;
use App\Models\Highlight;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class HighlightRequest extends FormRequest
{
    use ValidatesLocalizedText;

    public function authorize(): bool
    {
        $highlight = $this->route('block');

        return is_string($highlight)
            ? ($this->user()?->can('update', Highlight::query()->findOrFail($highlight)) ?? false)
            : ($this->user()?->can('create', Highlight::class) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'group' => ['required', Rule::enum(HighlightGroup::class)],
            'icon' => ['nullable', Rule::enum(ContentIcon::class)],
            // Metrics need a real figure; other groups have none.
            'figure' => ['nullable', 'string', 'max:20', 'required_if:group,'.HighlightGroup::Metric->value],
            ...$this->localizedRules('title', 80),
            ...$this->localizedRules('description', 300),
            'is_visible' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'group' => __('admin.highlights.fields.group'),
            'icon' => __('admin.fields.icon'),
            'figure' => __('admin.highlights.fields.figure'),
            ...$this->localizedAttributes('title', __('admin.fields.title')),
            ...$this->localizedAttributes('description', __('admin.highlights.fields.description')),
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
    public function highlightAttributes(): array
    {
        $group = HighlightGroup::from($this->string('group')->toString());

        return [
            'group' => $group,
            'icon' => $this->filled('icon') && $group !== HighlightGroup::Metric ? $this->string('icon')->toString() : null,
            'figure' => $group === HighlightGroup::Metric ? $this->string('figure')->trim()->toString() : null,
            'title' => $this->localizedText('title'),
            'description' => $this->localizedText('description'),
            'is_visible' => $this->boolean('is_visible'),
        ];
    }
}
