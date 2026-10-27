<?php

namespace App\Localization;

use Illuminate\Support\Arr;

/**
 * Interface strings the React pages translate in the browser: the given
 * translation groups (lang/{locale}/{group}.php) of one language, plus a
 * short version hash so a browser that already holds them (Inertia "once"
 * props) receives them again only after they change.
 *
 * A group may name part of a file ("site.errors"): only those lines are
 * sent, under the same keys as in PHP.
 */
class ClientTranslations
{
    /** @var array<string, array<string, mixed>> */
    private array $messages = [];

    /**
     * @param  list<string>  $groups
     * @return array<string, mixed>
     */
    public function messages(string $locale, array $groups): array
    {
        $key = $locale.'|'.implode(',', $groups);

        if (! isset($this->messages[$key])) {
            $messages = [];

            foreach ($groups as $group) {
                $lines = trans($group, [], $locale);
                Arr::set($messages, $group, is_array($lines) ? $lines : []);
            }

            $this->messages[$key] = $messages;
        }

        return $this->messages[$key];
    }

    /**
     * Key under which the browser remembers these strings.
     *
     * @param  list<string>  $groups
     */
    public function onceKey(string $locale, array $groups): string
    {
        $hash = hash('xxh128', (string) json_encode($this->messages($locale, $groups)));

        return 'i18n-'.$locale.'-'.substr($hash, 0, 12);
    }
}
