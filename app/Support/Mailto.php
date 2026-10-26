<?php

namespace App\Support;

/**
 * mailto: links (RFC 6068). Characters that are valid in an e-mail address
 * but structure a mailto URI (?, &, #, %) are percent-encoded, so an address
 * typed by a visitor cannot add recipients or headers to the reply.
 */
final class Mailto
{
    public static function link(string $address, ?string $subject = null): string
    {
        $link = 'mailto:'.str_replace(['%', '?', '&', '#'], ['%25', '%3F', '%26', '%23'], $address);

        return $subject === null ? $link : $link.'?subject='.rawurlencode($subject);
    }
}
