<?php

namespace App\Content;

/**
 * Editorial state of publishable content. Only published items with a
 * publication date that has passed are visible on the public site.
 */
enum PublicationStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return __("admin.status.{$this->value}");
    }

    /**
     * Badge tone used in the dashboard (text always accompanies the colour).
     */
    public function tone(): string
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Published => 'success',
            self::Archived => 'neutral',
        };
    }

    /**
     * @return array<string, string> value => label, for select inputs
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])->all();
    }
}
