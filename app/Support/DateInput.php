<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Dates typed into <input type="datetime-local"> fields. They are entered
 * in the display timezone (config wasmsoft.display_timezone) and stored in UTC.
 */
final class DateInput
{
    public const FORMAT = 'Y-m-d\TH:i';

    public static function toUtc(?string $value): ?CarbonImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $date = CarbonImmutable::createFromFormat(self::FORMAT, $value, (string) config('wasmsoft.display_timezone'));

        return $date === null ? null : $date->utc();
    }
}
