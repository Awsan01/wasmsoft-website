<?php

namespace App\View\Components\Ui;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

/**
 * Inline SVG icon from the curated set in resources/icons/lucide.php.
 *
 * Icons are decorative (aria-hidden) unless a label is given, in which case
 * the SVG is exposed to assistive technology with that accessible name.
 * Directional icons are mirrored automatically in right-to-left layouts.
 */
class Icon extends Component
{
    /** Icons whose meaning depends on reading direction. */
    public const DIRECTIONAL = ['arrow-left', 'arrow-right', 'arrow-up-right', 'chevron-left', 'chevron-right', 'log-in', 'log-out', 'send'];

    /** @var array<string, string>|null */
    private static ?array $icons = null;

    public function __construct(
        public string $name,
        public int $size = 20,
        public float $stroke = 1.75,
        public ?string $label = null,
    ) {}

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::$icons ??= require resource_path('icons/lucide.php');
    }

    public static function exists(string $name): bool
    {
        return array_key_exists($name, self::all());
    }

    public function paths(): string
    {
        return self::all()[$this->name]
            ?? throw new InvalidArgumentException("Unknown icon [{$this->name}].");
    }

    public function directional(): bool
    {
        return in_array($this->name, self::DIRECTIONAL, true);
    }

    public function render(): View|Closure|string
    {
        return view('components.ui.icon');
    }
}
