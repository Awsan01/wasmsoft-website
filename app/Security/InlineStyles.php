<?php

namespace App\Security;

/**
 * Collects SHA-256 hashes of the inline <style> blocks a response renders,
 * so the Content-Security-Policy allows exactly those blocks and nothing
 * else (see App\Http\Middleware\SecurityHeaders).
 *
 * Hashes are used instead of per-request nonces because a nonce changes
 * the HTML on every request: public pages would never revalidate (ETag)
 * and a cached page could end up paired with another response's policy.
 */
class InlineStyles
{
    /** @var array<string, true> */
    private array $hashes = [];

    /**
     * @param  string  $css  the exact text between <style> and </style>
     */
    public function allow(string $css): void
    {
        $this->hashes["'sha256-".base64_encode(hash('sha256', $css, true))."'"] = true;
    }

    /**
     * @return list<string> CSP source expressions
     */
    public function sources(): array
    {
        return array_keys($this->hashes);
    }
}
