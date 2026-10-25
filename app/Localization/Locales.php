<?php

namespace App\Localization;

use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use Illuminate\Http\Request;

/**
 * Registry of the locales the application supports and the subset that is
 * currently enabled (managed in Settings → Localization).
 *
 * Supported locales and their metadata (direction, ICU locale…) come from
 * config/wasmsoft.php; enabling a locale never requires code changes.
 */
class Locales
{
    public function __construct(private readonly SiteSettings $settings) {}

    /**
     * @return list<string>
     */
    public function supported(): array
    {
        // strval: PHP turns numeric-string array keys into integers.
        return array_map(strval(...), array_keys(config('wasmsoft.locales')));
    }

    /**
     * Enabled locales in display order. The default locale is always enabled.
     *
     * @return list<string>
     */
    public function enabled(): array
    {
        $configured = (array) $this->settings->get(SettingKey::EnabledLocales);
        $default = $this->default();

        return array_values(array_filter(
            $this->supported(),
            fn (string $code): bool => $code === $default || in_array($code, $configured, true),
        ));
    }

    public function default(): string
    {
        $default = $this->settings->get(SettingKey::DefaultLocale);

        if (is_string($default) && $this->isSupported($default)) {
            return $default;
        }

        $configured = config('wasmsoft.default_locale');

        return $this->isSupported($configured) ? $configured : $this->supported()[0];
    }

    public function isSupported(mixed $code): bool
    {
        return is_string($code) && array_key_exists($code, config('wasmsoft.locales'));
    }

    public function isEnabled(mixed $code): bool
    {
        return is_string($code) && in_array($code, $this->enabled(), true);
    }

    public function direction(string $code): string
    {
        return $this->definition($code)['dir'];
    }

    public function isRtl(string $code): bool
    {
        return $this->direction($code) === 'rtl';
    }

    public function nativeName(string $code): string
    {
        return $this->definition($code)['native'];
    }

    public function englishName(string $code): string
    {
        return $this->definition($code)['name'];
    }

    /** ICU locale used for number and date formatting. */
    public function intl(string $code): string
    {
        return $this->definition($code)['intl'];
    }

    /** Open Graph locale (language_TERRITORY). */
    public function openGraph(string $code): string
    {
        return $this->definition($code)['og'];
    }

    /** Regular expression matching any supported locale, for route constraints. */
    public function routePattern(): string
    {
        return implode('|', array_map(preg_quote(...), $this->supported()));
    }

    /**
     * Best locale for the visitor from the Accept-Language header, chosen
     * among $candidates (the enabled public locales by default).
     *
     * @param  list<string>|null  $candidates
     */
    public function negotiate(Request $request, ?array $candidates = null): string
    {
        $candidates ??= $this->enabled();

        foreach ($request->getLanguages() as $language) {
            $primary = strtolower(strtok(str_replace('_', '-', $language), '-') ?: '');

            if (in_array($primary, $candidates, true)) {
                return $primary;
            }
        }

        return in_array($this->default(), $candidates, true) ? $this->default() : $candidates[0];
    }

    /**
     * @return array{name: string, native: string, dir: string, intl: string, og: string}
     */
    private function definition(string $code): array
    {
        return config("wasmsoft.locales.{$code}")
            ?? throw new \InvalidArgumentException("Unsupported locale [{$code}].");
    }
}
