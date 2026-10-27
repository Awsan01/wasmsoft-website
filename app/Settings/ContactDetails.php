<?php

namespace App\Settings;

use App\Support\Mailto;
use App\Support\SocialNetwork;

/**
 * The public contact details configured in the dashboard, in one language.
 * Only details that have been filled in are ever shown.
 */
final readonly class ContactDetails
{
    /**
     * @param  list<array{network: SocialNetwork, url: string}>  $socialLinks
     */
    public function __construct(
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $hours,
        public ?string $mapUrl,
        public array $socialLinks,
    ) {}

    public static function from(SiteSettings $settings, string $locale): self
    {
        $text = fn (SettingKey $key): ?string => filled($value = $settings->get($key)) && is_string($value) ? $value : null;
        $mapUrl = $text(SettingKey::ContactMapUrl);

        return new self(
            email: $text(SettingKey::ContactEmail),
            phone: $text(SettingKey::ContactPhone),
            address: $settings->localized(SettingKey::ContactAddress, $locale),
            hours: $settings->localized(SettingKey::ContactHours, $locale),
            // Only web links are rendered (never javascript: or data: URLs).
            mapUrl: $mapUrl !== null && preg_match('#^https?://#i', $mapUrl) === 1 ? $mapUrl : null,
            socialLinks: SocialNetwork::linksFrom($settings->get(SettingKey::SocialLinks)),
        );
    }

    /**
     * The phone number as a tel: link (digits and a leading plus only).
     */
    public function phoneHref(): ?string
    {
        return $this->phone === null ? null : 'tel:'.preg_replace('/[^\d+]/', '', $this->phone);
    }

    public function hasDirectContact(): bool
    {
        return $this->email !== null || $this->phone !== null || $this->address !== null;
    }

    /**
     * The details as page data for the public site, links resolved.
     *
     * @return array{email: string|null, emailHref: string|null, phone: string|null, phoneHref: string|null, address: string|null, hours: string|null, mapUrl: string|null, social: list<array{network: string, label: string, icon: string, url: string}>}
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'emailHref' => $this->email === null ? null : Mailto::link($this->email),
            'phone' => $this->phone,
            'phoneHref' => $this->phoneHref(),
            'address' => $this->address,
            'hours' => $this->hours,
            'mapUrl' => $this->mapUrl,
            'social' => array_map(fn (array $link): array => [
                'network' => $link['network']->value,
                'label' => $link['network']->label(),
                'icon' => $link['network']->icon(),
                'url' => $link['url'],
            ], $this->socialLinks),
        ];
    }
}
