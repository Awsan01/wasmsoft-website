<?php

namespace App\Navigation;

/**
 * The menus of the public site.
 */
enum MenuLocation: string
{
    /** A flat list of links in the site header. */
    case Header = 'header';

    /** Groups (footer columns), each with its own links. */
    case Footer = 'footer';

    public function label(): string
    {
        return __("admin.navigation.menus.{$this->value}");
    }
}
