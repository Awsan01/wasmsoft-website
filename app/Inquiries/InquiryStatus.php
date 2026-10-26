<?php

namespace App\Inquiries;

/**
 * Where an inquiry stands. Messages that look automated are stored as
 * spam (not discarded), so a person caught by mistake can still be found.
 */
enum InquiryStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Closed = 'closed';
    case Spam = 'spam';

    public function label(): string
    {
        return __("admin.inquiries.status.{$this->value}");
    }

    /**
     * Badge tone in the dashboard (text always accompanies the colour).
     */
    public function tone(): string
    {
        return match ($this) {
            self::New => 'primary',
            self::InProgress => 'warning',
            self::Closed => 'success',
            self::Spam => 'neutral',
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
