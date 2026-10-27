/**
 * Interface strings in the browser, with the same rules as Laravel's
 * translator: dot keys into the translation groups Laravel sends
 * (lang/{locale}/{group}.php), `:name` placeholders (also `:Name` and
 * `:NAME`), and `trans_choice` pluralization (Illuminate\Translation\MessageSelector).
 */
export type Messages = { [key: string]: string | Messages };

export type Replacements = Record<string, string | number>;

export function lookup(messages: Messages, key: string): string | null {
    let current: string | Messages | undefined = messages;

    for (const segment of key.split('.')) {
        if (current === undefined || typeof current === 'string') {
            return null;
        }

        current = current[segment];
    }

    return typeof current === 'string' ? current : null;
}

const upperFirst = (text: string): string => text.charAt(0).toUpperCase() + text.slice(1);

/** Like Laravel's makeReplacements(): one pass (strtr), longest placeholder first. */
export function replace(line: string, replacements: Replacements = {}): string {
    const map = new Map<string, string>();

    for (const [key, raw] of Object.entries(replacements)) {
        const value = String(raw);

        map.set(`:${upperFirst(key)}`, upperFirst(value));
        map.set(`:${key.toUpperCase()}`, value.toUpperCase());
        map.set(`:${key}`, value);
    }

    if (map.size === 0) {
        return line;
    }

    const placeholders = [...map.keys()].sort((a, b) => b.length - a.length).map((key) => key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));

    return line.replace(new RegExp(placeholders.join('|'), 'g'), (match) => map.get(match) ?? match);
}

/** Missing keys show the key itself, as Laravel does, so they are noticed. */
export function translate(messages: Messages, key: string, replacements?: Replacements): string {
    return replace(lookup(messages, key) ?? key, replacements);
}

export function translateChoice(messages: Messages, key: string, count: number, locale: string, replacements: Replacements = {}): string {
    const line = lookup(messages, key);

    if (line === null) {
        return key;
    }

    return replace(choose(line, count, locale), { count, ...replacements });
}

/** Port of MessageSelector::choose(). */
export function choose(line: string, count: number, locale: string): string {
    const segments = line.split('|');

    for (const segment of segments) {
        const value = extract(segment, count);

        if (value !== null) {
            return value.trim();
        }
    }

    const stripped = segments.map((segment) => segment.replace(/^[{[]([-?\d|*,.*]*)[}\]]/, ''));
    const index = pluralIndex(locale, count);

    if (stripped.length === 1 || stripped[index] === undefined) {
        return stripped[0] ?? line;
    }

    return stripped[index];
}

function extract(segment: string, count: number): string | null {
    const match = /^[{[]([-?\d|*,.*]*)[}\]](.*)/s.exec(segment);

    if (match === null) {
        return null;
    }

    const condition = match[1] ?? '';
    const value = match[2] ?? '';

    if (condition.includes(',')) {
        const [from = '', to = ''] = condition.split(',', 2);

        if (to === '*' && count >= Number(from)) {
            return value;
        }

        if (from === '*' && count <= Number(to)) {
            return value;
        }

        if (count >= Number(from) && count <= Number(to)) {
            return value;
        }
    }

    return Number(condition) === count && condition !== '' ? value : null;
}

/** Plural forms of the site's languages (MessageSelector::getPluralIndex). */
export function pluralIndex(locale: string, count: number): number {
    const number = Math.abs(count);
    const language = locale.split(/[-_]/)[0];

    switch (language) {
        case 'ar': {
            const lastTwo = Math.trunc(number) % 100;

            if (number === 0) return 0;
            if (number === 1) return 1;
            if (number === 2) return 2;
            if (lastTwo >= 3 && lastTwo <= 10) return 3;
            if (lastTwo >= 11 && lastTwo <= 99) return 4;

            return 5;
        }
        case 'en':
            return number === 1 ? 0 : 1;
        case 'tr':
        default:
            return 0;
    }
}
