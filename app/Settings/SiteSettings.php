<?php

namespace App\Settings;

use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\DB;

/**
 * Cached access to the site settings table.
 *
 * All settings are loaded with one query and cached until they change.
 */
class SiteSettings
{
    private const CACHE_KEY = 'site-settings:v1';

    /** @var array<string, mixed>|null */
    private ?array $values = null;

    public function __construct(private readonly Cache $cache) {}

    public function get(SettingKey $key): mixed
    {
        $values = $this->all();

        return array_key_exists($key->value, $values) && $values[$key->value] !== null
            ? $values[$key->value]
            : $key->default();
    }

    /**
     * Text of a localized setting in the given locale.
     *
     * With $fallback, a missing translation falls back to $fallbackLocale;
     * callers rendering that text mark it with the fallback's lang attribute.
     */
    public function localized(SettingKey $key, string $locale, ?string $fallbackLocale = null): ?string
    {
        $value = $this->get($key);

        if (! is_array($value)) {
            return filled($value) ? (string) $value : null;
        }

        if (filled($value[$locale] ?? null)) {
            return $value[$locale];
        }

        return $fallbackLocale !== null && filled($value[$fallbackLocale] ?? null) ? $value[$fallbackLocale] : null;
    }

    /**
     * Persist several settings atomically.
     *
     * @param  array<string, mixed>  $values  setting key value => value
     */
    public function put(array $values): void
    {
        foreach (array_keys($values) as $key) {
            SettingKey::from($key);
        }

        DB::transaction(function () use ($values): void {
            foreach ($values as $key => $value) {
                Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
            }
        });

        $this->flush();
    }

    public function flush(): void
    {
        $this->values = null;
        $this->cache->forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    private function all(): array
    {
        return $this->values ??= $this->cache->rememberForever(
            self::CACHE_KEY,
            fn (): array => Setting::query()->pluck('value', 'key')->all(),
        );
    }
}
