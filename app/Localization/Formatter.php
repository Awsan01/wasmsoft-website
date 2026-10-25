<?php

namespace App\Localization;

use Carbon\CarbonInterface;
use IntlDateFormatter;
use NumberFormatter;

/**
 * Locale-aware number and date formatting.
 *
 * Uses each locale's ICU identifier from config/wasmsoft.php, which is
 * where the digit system is chosen (Arabic uses Western digits by default).
 */
class Formatter
{
    private const DATE_STYLES = [
        'short' => IntlDateFormatter::SHORT,
        'medium' => IntlDateFormatter::MEDIUM,
        'long' => IntlDateFormatter::LONG,
    ];

    public function __construct(private readonly Locales $locales) {}

    public function number(int|float $value, ?string $locale = null): string
    {
        $formatter = new NumberFormatter($this->intl($locale), NumberFormatter::DECIMAL);

        return (string) $formatter->format($value);
    }

    /**
     * @param  'short'|'medium'|'long'  $style
     */
    public function date(CarbonInterface $date, string $style = 'long', ?string $locale = null): string
    {
        return $this->format($date, self::DATE_STYLES[$style], IntlDateFormatter::NONE, $locale);
    }

    /**
     * @param  'short'|'medium'|'long'  $style
     */
    public function dateTime(CarbonInterface $date, string $style = 'medium', ?string $locale = null): string
    {
        return $this->format($date, self::DATE_STYLES[$style], IntlDateFormatter::SHORT, $locale);
    }

    /** "1.4 MB": binary units, one decimal from megabytes up. */
    public function fileSize(int $bytes, ?string $locale = null): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;

        $formatter = new NumberFormatter($this->intl($locale), NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $power >= 2 ? 1 : 0);

        return $formatter->format($bytes / (1024 ** $power)).' '.$units[$power];
    }

    /** "3 days ago" in the requested language. */
    public function relative(CarbonInterface $date, ?string $locale = null): string
    {
        return $date->copy()->locale($locale ?? app()->getLocale())->diffForHumans();
    }

    private function format(CarbonInterface $date, int $dateStyle, int $timeStyle, ?string $locale): string
    {
        $timezone = (string) config('wasmsoft.display_timezone');
        $formatter = new IntlDateFormatter($this->intl($locale), $dateStyle, $timeStyle, $timezone);

        return (string) $formatter->format($date->toDateTimeImmutable());
    }

    private function intl(?string $locale): string
    {
        $locale ??= app()->getLocale();

        return $this->locales->isSupported($locale) ? $this->locales->intl($locale) : $locale;
    }
}
