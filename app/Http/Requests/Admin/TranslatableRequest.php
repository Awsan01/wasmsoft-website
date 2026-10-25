<?php

namespace App\Http\Requests\Admin;

use App\Content\Slug;
use App\Content\TranslatableContent;
use App\Localization\Locales;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation for content edited in every language at once.
 *
 * The form posts `translations[{locale}][{field}]` for each supported
 * locale. A language version is optional as a whole, but once any of its
 * fields is filled, its required fields must be too; at least one
 * language must be filled in. Slugs are normalized, checked against
 * reserved words and must be unique within their language.
 */
abstract class TranslatableRequest extends FormRequest
{
    /**
     * Validation rules of each translatable field, applied in every language.
     *
     * @return array<string, list<mixed>>
     */
    abstract protected function translationFields(): array;

    /**
     * The content being validated: the bound model when editing, a new
     * instance when creating (used for slug uniqueness).
     */
    abstract protected function content(): Model&TranslatableContent;

    /**
     * @return list<string>
     */
    protected function requiredTranslationFields(): array
    {
        return ['title'];
    }

    /**
     * Slugs that would collide with fixed routes.
     *
     * @return list<string>
     */
    protected function reservedSlugs(): array
    {
        return [];
    }

    /**
     * Fields edited as one item per line (a textarea) and stored as a list,
     * e.g. the benefits of a service.
     *
     * @return list<string>
     */
    protected function lineFields(): array
    {
        return [];
    }

    protected function prepareForValidation(): void
    {
        $translations = $this->input('translations');

        if (! is_array($translations)) {
            return;
        }

        $hasSlug = array_key_exists('slug', $this->translationFields());

        foreach ($translations as $locale => $fields) {
            if (! is_array($fields)) {
                continue;
            }

            if ($hasSlug && is_string($fields['slug'] ?? null) && trim($fields['slug']) !== '') {
                $translations[$locale]['slug'] = Slug::make($fields['slug'], (string) $locale);
            }

            foreach ($this->lineFields() as $field) {
                if (is_string($fields[$field] ?? null)) {
                    $lines = array_map(trim(...), preg_split('/\R/u', $fields[$field]) ?: []);
                    $translations[$locale][$field] = array_values(array_filter($lines, fn (string $line): bool => $line !== ''));
                }
            }
        }

        $this->merge(['translations' => $translations]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function translationRules(): array
    {
        $locales = $this->locales()->supported();
        // Nested rules ("benefits.*") apply to list items, not to separate fields.
        $fields = array_values(array_filter(array_keys($this->translationFields()), fn (string $field): bool => ! str_contains($field, '.')));
        $rules = ['translations' => ['required', 'array:'.implode(',', $locales)]];

        foreach ($locales as $locale) {
            $rules["translations.{$locale}"] = ['nullable', 'array:'.implode(',', $fields)];

            foreach ($this->translationFields() as $field => $fieldRules) {
                if (str_contains($field, '.')) {
                    $rules["translations.{$locale}.{$field}"] = $fieldRules;

                    continue;
                }

                $others = collect($fields)
                    ->reject(fn (string $other): bool => $other === $field)
                    ->map(fn (string $other): string => "translations.{$locale}.{$other}")
                    ->implode(',');

                $required = in_array($field, $this->requiredTranslationFields(), true) && $others !== ''
                    ? ["required_with:{$others}"]
                    : [];

                $rules["translations.{$locale}.{$field}"] = ['nullable', ...$required, ...$fieldRules];
            }
        }

        return $rules;
    }

    /**
     * @return list<mixed>
     */
    protected function slugRules(): array
    {
        return ['string', 'max:'.Slug::MAX_LENGTH, 'regex:'.Slug::PATTERN, Rule::notIn($this->reservedSlugs())];
    }

    /**
     * Readable field names, e.g. "Title (العربية)".
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->locales()->supported() as $locale) {
            foreach (array_keys($this->translationFields()) as $field) {
                $attributes["translations.{$locale}.{$field}"] = __('admin.translations.field', [
                    'field' => __('admin.fields.'.strtok($field, '.')),
                    'language' => $this->locales()->nativeName($locale),
                ]);
            }
        }

        return $attributes;
    }

    /**
     * @return list<\Closure(Validator): void>
     */
    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateTranslations($validator)];
    }

    /**
     * Validated translation input: locale => field => value.
     *
     * @return array<string, array<string, mixed>>
     */
    public function translations(): array
    {
        $translations = $this->validated('translations', []);

        return array_map(fn (mixed $fields): array => is_array($fields) ? $fields : [], is_array($translations) ? $translations : []);
    }

    private function validateTranslations(Validator $validator): void
    {
        $translations = $this->input('translations');
        $translations = is_array($translations) ? $translations : [];
        $required = $this->requiredTranslationFields()[0] ?? null;

        $filled = array_filter($translations, fn (mixed $fields): bool => is_array($fields) && $required !== null && filled($fields[$required] ?? null));

        if ($required !== null && $filled === []) {
            $validator->errors()->add("translations.{$this->locales()->default()}.{$required}", __('admin.translations.at_least_one'));
        }

        if (! array_key_exists('slug', $this->translationFields())) {
            return;
        }

        $content = $this->content();

        foreach ($translations as $locale => $fields) {
            $slug = is_array($fields) ? ($fields['slug'] ?? null) : null;
            $key = "translations.{$locale}.slug";

            if (! is_string($slug) || $slug === '' || $validator->errors()->has($key)) {
                continue;
            }

            $existing = $content->exists
                ? $content->translations()->where('locale', $locale)->first()
                : null;

            if ($content->slugTaken($slug, (string) $locale, $existing instanceof Translation ? $existing : null)) {
                $validator->errors()->add($key, __('admin.translations.slug_taken'));
            }
        }
    }

    private function locales(): Locales
    {
        return app(Locales::class);
    }
}
