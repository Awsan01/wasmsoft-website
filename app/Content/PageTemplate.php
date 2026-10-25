<?php

namespace App\Content;

/**
 * Layout used to render a custom page on the public site.
 */
enum PageTemplate: string
{
    case Default = 'default';
    case About = 'about';

    public function label(): string
    {
        return __("admin.pages.templates.{$this->value}");
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $template): array => [$template->value => $template->label()])->all();
    }
}
