# 0002 — Translation tables for routable content, no silent language fallback

Status: accepted (2026-10-25)

## Context

Content exists in Arabic, English and Turkish, but not every item will be written in every language. Each language needs its own address (`/ar/…`, `/en/…`, `/tr/…`) and its own slug, title and metadata. Showing English text on an Arabic page, or offering a language switch to a page that does not exist, would mislead visitors and search engines.

## Decision

- Content that has its own address (pages, services, projects, articles, news and project categories, tags) keeps one row per item and one row per language in a `*_translations` table (`HasTranslations`): title, slug, summary, body and SEO fields. Slugs are unique per language.
- Short texts that never have an address (highlights, testimonials, team members, milestones, menu labels, image descriptions, settings) are stored as JSON maps of language to text (`HasLocalizedText`).
- There is no automatic fallback to another language. Lists only show items translated into the page's language. Opening an item:
  - with its slug in this language: shows it;
  - with a slug from another language: redirects (301) to this language's version if it exists;
  - when it exists only in other languages: answers 404 with links to the versions that exist;
  - otherwise: 404.
- `hreflang` links, the language switcher and the sitemap only list languages in which the item exists.
- Interface texts live in `lang/{ar,en,tr}`; a test checks that the three files have the same keys.

## Consequences

- Editors see which languages are missing (the overview's missing-translations list and the language column of every content list), and nothing appears half-translated.
- Adding a fourth language needs no schema change: add it to `config/wasmsoft.php`, translate a new `lang/` folder, extend the translation parity test, add a font if the script needs one, and write the content.
- Queries must always say which language they want (`translatedIn`, `withTranslation`), which the controllers and tests do consistently.
