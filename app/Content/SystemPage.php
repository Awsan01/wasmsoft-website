<?php

namespace App\Content;

/**
 * Pages whose URL is fixed by the application (the home page and the
 * listing pages). Editors manage their headline, introduction and search
 * metadata; they cannot be unpublished, renamed or deleted.
 */
enum SystemPage: string
{
    case Home = 'home';
    case Services = 'services';
    case Projects = 'projects';
    case News = 'news';
    case Contact = 'contact';

    public function routeName(): string
    {
        return match ($this) {
            self::Home => 'site.home',
            self::Services => 'site.services.index',
            self::Projects => 'site.projects.index',
            self::News => 'site.news.index',
            self::Contact => 'site.contact',
        };
    }

    public function label(): string
    {
        return __("admin.pages.system.{$this->value}");
    }

    /**
     * Name of the page in the site menus.
     */
    public function navLabel(string $locale): string
    {
        $label = __("site.nav.{$this->value}", [], $locale);

        return is_string($label) ? $label : $this->value;
    }

    /**
     * Route names under which a menu link to this page counts as active.
     */
    public function activePattern(): string
    {
        return match ($this) {
            self::Home => 'site.home',
            self::Contact => 'site.contact',
            default => str_replace('.index', '.*', $this->routeName()),
        };
    }
}
