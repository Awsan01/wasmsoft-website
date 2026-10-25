<?php

namespace App\Content;

/**
 * Content with its own public URL in each language it is translated into.
 */
interface PubliclyRoutable
{
    /**
     * The public URL of this item in $locale, or null when it has no
     * published translation there.
     */
    public function publicUrl(string $locale): ?string;
}
