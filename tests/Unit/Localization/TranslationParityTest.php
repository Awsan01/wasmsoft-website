<?php

namespace Tests\Unit\Localization;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Every interface string must exist, be filled in and use the same
 * placeholders in Arabic, English and Turkish, so no screen silently
 * falls back to another language.
 */
class TranslationParityTest extends TestCase
{
    private const LOCALES = ['ar', 'en', 'tr'];

    private const REFERENCE = 'en';

    private static function langPath(string $path = ''): string
    {
        return dirname(__DIR__, 3).'/lang'.($path === '' ? '' : '/'.$path);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function phpFiles(): array
    {
        $files = array_map('basename', glob(self::langPath(self::REFERENCE.'/*.php')) ?: []);

        return array_combine($files, array_map(fn (string $file): array => [$file], $files));
    }

    public function test_every_locale_has_the_same_language_files(): void
    {
        $reference = array_keys(self::phpFiles());

        foreach (self::LOCALES as $locale) {
            $files = array_map('basename', glob(self::langPath("{$locale}/*.php")) ?: []);
            sort($files);
            sort($reference);

            $this->assertSame($reference, $files, "lang/{$locale} does not contain the same files as lang/en.");
        }
    }

    #[DataProvider('phpFiles')]
    public function test_file_has_identical_keys_and_placeholders_in_every_locale(string $file): void
    {
        $reference = $this->flatten(require self::langPath(self::REFERENCE."/{$file}"));

        foreach (self::LOCALES as $locale) {
            $strings = $this->flatten(require self::langPath("{$locale}/{$file}"));

            $this->assertSame([], array_values(array_diff(array_keys($reference), array_keys($strings))), "Missing keys in lang/{$locale}/{$file}.");
            $this->assertSame([], array_values(array_diff(array_keys($strings), array_keys($reference))), "Unknown keys in lang/{$locale}/{$file}.");

            foreach ($strings as $key => $value) {
                $this->assertNotSame('', trim($value), "Empty string for {$file}:{$key} in {$locale}.");
                $this->assertSame($this->placeholders($reference[$key]), $this->placeholders($value), "Placeholders differ for {$file}:{$key} in {$locale}.");
            }
        }
    }

    public function test_json_translations_match_their_keys(): void
    {
        $locales = array_values(array_diff(self::LOCALES, [self::REFERENCE]));
        $keySets = [];

        foreach ($locales as $locale) {
            $strings = json_decode((string) file_get_contents(self::langPath("{$locale}.json")), true, flags: JSON_THROW_ON_ERROR);
            $keySets[$locale] = array_keys($strings);

            foreach ($strings as $key => $value) {
                $this->assertNotSame('', trim($value), "Empty JSON translation for \"{$key}\" in {$locale}.");
                $this->assertSame($this->placeholders($key), $this->placeholders($value), "Placeholders differ for \"{$key}\" in {$locale}.json.");
            }
        }

        $this->assertSame($keySets[$locales[0]], $keySets[$locales[1]], 'JSON translation files contain different keys.');
    }

    /**
     * @param  array<string, mixed>  $strings
     * @return array<string, string>
     */
    private function flatten(array $strings, string $prefix = ''): array
    {
        $flat = [];

        foreach ($strings as $key => $value) {
            if (is_array($value)) {
                $flat += $this->flatten($value, "{$prefix}{$key}.");
            } else {
                $flat["{$prefix}{$key}"] = (string) $value;
            }
        }

        return $flat;
    }

    /**
     * @return list<string>
     */
    private function placeholders(string $value): array
    {
        preg_match_all('/:([A-Za-z_]+)/', $value, $matches);
        $names = array_values(array_unique($matches[1]));
        sort($names);

        return $names;
    }
}
