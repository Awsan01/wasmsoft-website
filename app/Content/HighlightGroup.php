<?php

namespace App\Content;

/**
 * Where a highlight appears: differentiators and metrics on the home page,
 * values on the about page, ways to partner on the partnerships page.
 */
enum HighlightGroup: string
{
    case Differentiator = 'differentiator';
    case Value = 'value';
    case Metric = 'metric';
    case Partnership = 'partnership';

    public function label(): string
    {
        return __("admin.highlights.groups.{$this->value}.label");
    }

    public function description(): string
    {
        return __("admin.highlights.groups.{$this->value}.description");
    }
}
