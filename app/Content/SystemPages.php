<?php

namespace App\Content;

use App\Models\Page;

/**
 * Makes sure every application-defined page has its record, so editors
 * always find the home page and listing introductions in the dashboard.
 * Public pages also work without them (they fall back to interface text).
 */
class SystemPages
{
    public function ensure(): void
    {
        $existing = Page::query()->whereNotNull('system_key')->pluck('system_key')->all();

        foreach (SystemPage::cases() as $systemPage) {
            if (in_array($systemPage, $existing, true)) {
                continue;
            }

            $page = new Page;
            $page->forceFill(['system_key' => $systemPage]);
            $page->applyStatus(PublicationStatus::Published);
            $page->save();
        }
    }
}
