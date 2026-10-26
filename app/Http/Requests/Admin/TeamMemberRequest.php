<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesLocalizedText;
use App\Media\MediaKind;
use App\Models\TeamMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TeamMemberRequest extends FormRequest
{
    use ValidatesLocalizedText;

    public function authorize(): bool
    {
        $member = $this->route('block');

        return is_string($member)
            ? ($this->user()?->can('update', TeamMember::query()->findOrFail($member)) ?? false)
            : ($this->user()?->can('create', TeamMember::class) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            ...$this->localizedRules('role', 120),
            ...$this->localizedRules('bio', 600),
            'photo_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where('kind', MediaKind::Image->value)],
            'is_visible' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('admin.fields.name'),
            ...$this->localizedAttributes('role', __('admin.team.fields.role')),
            ...$this->localizedAttributes('bio', __('admin.team.fields.bio')),
            'photo_id' => __('admin.team.fields.photo'),
            'is_visible' => __('admin.blocks.visible_label'),
        ];
    }

    /**
     * @return list<\Closure(Validator): void>
     */
    public function after(): array
    {
        return [fn (Validator $validator) => $this->requireInSomeLanguage($validator, 'role')];
    }

    /**
     * @return array<string, mixed>
     */
    public function memberAttributes(): array
    {
        return [
            'name' => $this->string('name')->trim()->toString(),
            'role' => $this->localizedText('role'),
            'bio' => $this->localizedText('bio'),
            'photo_id' => $this->filled('photo_id') ? $this->integer('photo_id') : null,
            'is_visible' => $this->boolean('is_visible'),
        ];
    }
}
