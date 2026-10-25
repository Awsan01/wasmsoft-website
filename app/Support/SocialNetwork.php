<?php

namespace App\Support;

/**
 * Social networks that can be linked from the site footer.
 */
enum SocialNetwork: string
{
    case LinkedIn = 'linkedin';
    case X = 'x';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case GitHub = 'github';
    case YouTube = 'youtube';

    public function label(): string
    {
        return match ($this) {
            self::LinkedIn => 'LinkedIn',
            self::X => 'X',
            self::Facebook => 'Facebook',
            self::Instagram => 'Instagram',
            self::GitHub => 'GitHub',
            self::YouTube => 'YouTube',
        };
    }

    public function icon(): string
    {
        return $this === self::X ? 'x-logo' : $this->value;
    }

    /**
     * Turn the stored setting into renderable links, skipping anything
     * malformed or not using http(s).
     *
     * @return list<array{network: self, url: string}>
     */
    public static function linksFrom(mixed $stored): array
    {
        $links = [];

        foreach (is_array($stored) ? $stored : [] as $item) {
            $network = self::tryFrom((string) ($item['network'] ?? ''));
            $url = (string) ($item['url'] ?? '');

            if ($network !== null && preg_match('#^https?://#i', $url) === 1) {
                $links[] = ['network' => $network, 'url' => $url];
            }
        }

        return $links;
    }
}
