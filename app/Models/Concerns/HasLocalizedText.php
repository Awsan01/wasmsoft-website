<?php

namespace App\Models\Concerns;

use App\Localization\Locales;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Short texts stored as a locale => text map in a JSON column (highlights,
 * testimonials, team roles…), cast to an array. A text that was not written
 * in a language is missing there: nothing falls back to another language.
 */
trait HasLocalizedText
{
    /**
     * Only items whose $attribute (a column name from code) is written in $locale.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function writtenIn(Builder $query, string $attribute, string $locale): void
    {
        $query->whereNotNull("{$attribute}->{$locale}")->where("{$attribute}->{$locale}", '!=', '');
    }

    /**
     * Languages in which $attribute is written.
     *
     * @return list<string>
     */
    protected function localesWith(string $attribute): array
    {
        $value = $this->getAttribute($attribute);
        $locales = [];

        foreach (is_array($value) ? $value : [] as $locale => $text) {
            if (is_string($text) && trim($text) !== '') {
                $locales[] = (string) $locale;
            }
        }

        return $locales;
    }

    /**
     * The text of $attribute in $locale (the current one by default), or
     * null when it was not written in that language.
     */
    public function localized(string $attribute, ?string $locale = null): ?string
    {
        $value = $this->getAttribute($attribute);
        $text = is_array($value) ? ($value[$locale ?? app()->getLocale()] ?? null) : null;

        return is_string($text) && trim($text) !== '' ? $text : null;
    }

    /**
     * The text to name the item by in the dashboard: the dashboard language,
     * else the site's default language, else any written language.
     */
    public function displayText(string $attribute): ?string
    {
        foreach ([app()->getLocale(), app(Locales::class)->default()] as $locale) {
            if (($text = $this->localized($attribute, $locale)) !== null) {
                return $text;
            }
        }

        $value = $this->getAttribute($attribute);

        foreach (is_array($value) ? $value : [] as $text) {
            if (is_string($text) && trim($text) !== '') {
                return $text;
            }
        }

        return null;
    }
}
