<?php

namespace App\Http\Requests\Admin;

use App\Localization\Locales;
use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $media = $this->route('media');

        return $media instanceof Media && ($this->user()?->can('update', $media) ?? false);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $locales = app(Locales::class)->supported();
        $rules = ['alt' => ['nullable', 'array:'.implode(',', $locales)]];

        foreach ($locales as $locale) {
            $rules["alt.{$locale}"] = ['nullable', 'string', 'max:250'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $locales = app(Locales::class);
        $attributes = [];

        foreach ($locales->supported() as $locale) {
            $attributes["alt.{$locale}"] = __('admin.translations.field', [
                'field' => __('admin.fields.alt'),
                'language' => $locales->nativeName($locale),
            ]);
        }

        return $attributes;
    }

    /**
     * Alternative texts without empty entries.
     *
     * @return array<string, string>
     */
    public function altTexts(): array
    {
        $alt = $this->validated('alt', []);

        return array_filter(is_array($alt) ? $alt : [], fn (mixed $text): bool => is_string($text) && trim($text) !== '');
    }
}
