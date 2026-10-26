<?php

namespace App\Security;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Spam protection for public forms without third-party services, cookies
 * or tracking:
 *
 *  - a honeypot field that people never see, so never fill in;
 *  - an encrypted timestamp of when the form was shown. Forms sent within
 *    a few seconds look automated; forms older than a couple of hours must
 *    be reloaded, which limits replaying one captured form.
 *
 * Suspicious submissions are not refused visibly — that only teaches bots
 * what to change — but kept as spam, so a person caught by mistake is not
 * lost. Rate limiting (throttle:contact) caps the volume.
 */
final class SpamGuard
{
    public const HONEYPOT = 'website';

    public const TOKEN = 'form_token';

    public function token(): string
    {
        return Crypt::encryptString((string) now()->getTimestamp());
    }

    /**
     * The token for a form shown again after errors: the submitted one while
     * it is valid (the time already spent on the form keeps counting),
     * otherwise a new one.
     */
    public function tokenFor(mixed $submitted): string
    {
        return is_string($submitted) && $this->isValid($submitted) ? $submitted : $this->token();
    }

    /**
     * Issued by this application and not expired.
     */
    public function isValid(mixed $token): bool
    {
        $age = $this->age($token);

        return $age !== null && $age <= (int) config('wasmsoft.contact.max_age_minutes') * 60;
    }

    public function isSuspicious(Request $request): bool
    {
        $age = $this->age($request->input(self::TOKEN));

        return filled($request->input(self::HONEYPOT))
            || $age === null
            || $age < (int) config('wasmsoft.contact.min_seconds');
    }

    /**
     * Seconds since the form was shown, or null for a missing or forged token.
     */
    private function age(mixed $token): ?int
    {
        if (! is_string($token) || $token === '') {
            return null;
        }

        try {
            $issued = Crypt::decryptString($token);
        } catch (DecryptException) {
            return null;
        }

        if (! ctype_digit($issued)) {
            return null;
        }

        $age = now()->getTimestamp() - (int) $issued;

        return $age >= 0 ? $age : null;
    }
}
