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
}
