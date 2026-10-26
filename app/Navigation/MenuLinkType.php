<?php

namespace App\Navigation;

/**
 * What a menu item points to. Only these destinations exist, so a menu can
 * never link to an arbitrary scheme (javascript:, data:…) or a dashboard page.
 */
enum MenuLinkType: string
{
    /** A page with a fixed address (home, services, projects, news, contact). */
    case System = 'system';

    /** A custom page created in the dashboard. */
    case Page = 'page';

    /** An external http(s) address. */
    case Url = 'url';

    /** A footer column heading; its links are its children. */
    case Group = 'group';

    public function label(): string
    {
        return __("admin.navigation.types.{$this->value}");
    }
}
