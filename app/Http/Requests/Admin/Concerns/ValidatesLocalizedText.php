<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Localization\Locales;
use Illuminate\Validation\Validator;

/**
 * Validation of locale => text maps edited with <x-admin.localized-input>
 * (fields named `{field}[{locale}]`).
 */
trait ValidatesLocalizedText
{
    /**
     * @return array<string, list<mixed>>
     */
    protected function localizedRules(string $field, int $max): array
    {
        return [
            $field => ['nullable', 'array:'.implode(',', $this->supportedLocales())],
            $field.'.*' => ['nullable', 'string', 'max:'.$max],
        ];
    }

    /**
     * Readable names, e.g. "Title (العربية)".
     *
     * @return array<string, string>
     */
    protected function localizedAttributes(string $field, string $label): array
    {
        $locales = app(Locales::class);
        $attributes = [$field => $label];

        foreach ($locales->supported() as $locale) {
            $attributes["{$field}.{$locale}"] = __('admin.translations.field', ['field' => $label, 'language' => $locales->nativeName($locale)]);
        }

        return $attributes;
    }

    /**
     * Require $field in at least one language (the error is shown on the default language).
     */
    protected function requireInSomeLanguage(Validator $validator, string $field): void
    {
        if ($this->localizedText($field) === null) {
            $validator->errors()->add($field.'.'.app(Locales::class)->default(), __('admin.translations.at_least_one'));
        }
    }

    /**
     * The submitted texts without empty languages, or null when none was written.
     *
     * @return array<string, string>|null
     */
    protected function localizedText(string $field): ?array
    {
        $texts = [];

        foreach ((array) $this->input($field, []) as $locale => $text) {
            if (in_array($locale, $this->supportedLocales(), true) && is_string($text) && trim($text) !== '') {
                $texts[$locale] = trim($text);
            }
        }

        return $texts === [] ? null : $texts;
    }

    /**
     * @return list<string>
     */
    private function supportedLocales(): array
    {
        return app(Locales::class)->supported();
    }
}
