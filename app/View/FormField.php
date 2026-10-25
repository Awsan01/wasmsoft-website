<?php

namespace App\View;

use Illuminate\Support\ViewErrorBag;

/**
 * Helpers shared by the form control components.
 *
 * Public pages are served without a session (see bootstrap/app.php), so
 * old input and the validation error bag are read defensively.
 */
final class FormField
{
    /**
     * Convert an HTML field name to the dot-notation key used by validation
     * and old input: "translations[en][title]" becomes "translations.en.title".
     */
    public static function key(string $name): string
    {
        $key = str_replace(['[]', '[', ']'], ['', '.', ''], $name);

        return trim($key, '.');
    }

    public static function id(string $name): string
    {
        return 'field-'.str_replace(['.', '_'], '-', self::key($name));
    }

    public static function old(string $name, mixed $default = null): mixed
    {
        $request = request();

        return $request->hasSession() ? $request->old(self::key($name), $default) : $default;
    }

    public static function error(string $name): ?string
    {
        $errors = view()->shared('errors');

        if (! $errors instanceof ViewErrorBag) {
            return null;
        }

        return $errors->first(self::key($name)) ?: null;
    }

    /**
     * Build the aria-describedby value for a control.
     */
    public static function describedBy(string $id, bool $hasHint, bool $hasError): ?string
    {
        $ids = array_filter([
            $hasHint ? $id.'-hint' : null,
            $hasError ? $id.'-error' : null,
        ]);

        return $ids === [] ? null : implode(' ', $ids);
    }
}
