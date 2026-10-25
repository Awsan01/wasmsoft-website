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

    /** "3 days ago" in the requested language. */
    public function relative(CarbonInterface $date, ?string $locale = null): string
    {
        return $date->copy()->locale($locale ?? app()->getLocale())->diffForHumans();
    }

    private function format(CarbonInterface $date, int $dateStyle, int $timeStyle, ?string $locale): string
    {
        $timezone = $date->getTimezone();
        $formatter = new IntlDateFormatter($this->intl($locale), $dateStyle, $timeStyle, $timezone);

        return (string) $formatter->format($date->toDateTimeImmutable());
    }

    private function intl(?string $locale): string
    {
        $locale ??= app()->getLocale();

        return $this->locales->isSupported($locale) ? $this->locales->intl($locale) : $locale;
    }
}
