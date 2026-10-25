<?php

namespace App\Navigation;

/**
 * A dashboard sidebar entry, resolved for the current user and request.
 */
final readonly class AdminLink
{
    public function __construct(
        public string $label,
        public string $url,
        public string $icon,
        public bool $active = false,
        public ?int $badge = null,
    ) {}
}
