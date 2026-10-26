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
     * Domains a profile link of this network may point to (subdomains included).
     *
     * @return list<string>
     */
    public function hosts(): array
    {
        return match ($this) {
            self::LinkedIn => ['linkedin.com'],
            self::X => ['x.com', 'twitter.com'],
            self::Facebook => ['facebook.com', 'fb.com'],
            self::Instagram => ['instagram.com'],
            self::GitHub => ['github.com'],
            self::YouTube => ['youtube.com', 'youtu.be'],
        };
    }

    /**
     * Whether $url is an https link to this network.
     */
    public function allows(string $url): bool
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (($parts['scheme'] ?? null) !== 'https' || $host === '') {
            return false;
        }

        foreach ($this->hosts() as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                return true;
            }
        }

        return false;
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
