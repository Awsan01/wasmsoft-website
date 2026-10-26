<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Dates typed into <input type="datetime-local"> fields. They are entered
 * in the display timezone (config wasmsoft.display_timezone) and stored in UTC.
 */
final class DateInput
{
    public const FORMAT = 'Y-m-d\TH:i';

    /**
     * The field value of a stored date.
     */
    public static function format(CarbonInterface $date): string
    {
        return $date->copy()->setTimezone((string) config('wasmsoft.display_timezone'))->format(self::FORMAT);
    }

    public static function toUtc(?string $value): ?CarbonImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $date = CarbonImmutable::createFromFormat(self::FORMAT, $value, (string) config('wasmsoft.display_timezone'));

        return $date === null ? null : $date->utc();
    }
}
