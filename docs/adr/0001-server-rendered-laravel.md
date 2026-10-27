# 0001 — A server-rendered Laravel application, without a front-end framework

Status: replaced by [0008](0008-react-pages-with-inertia.md): for the public site on 2026-10-27, for the dashboard on 2026-10-28.

## Context

The repository started as an empty Laravel 13 skeleton. The site is mostly content: pages that must load fast, be indexed by search engines and work in three languages, one of them right to left. The dashboard is forms and lists for a small team. The brief asks for long-term maintainability over novelty, and for no unnecessary packages.

## Decision

- Laravel 13 renders every page with Blade. Reusable pieces are Blade components (`resources/views/components/{ui,site,admin}`).
- Styling is Tailwind CSS 4 on top of design tokens in `resources/css/tokens.css`; components use logical properties (`ms-`, `inset-s-`…) so the same markup serves left-to-right and right-to-left.
- Interactivity is a handful of small JavaScript modules (dialogs, dropdowns, tabs, the media picker, the mobile menu) loaded by Vite. Forms are saved by normal page submissions, and lists are reordered with up and down buttons rather than drag and drop.
- No front-end framework, no CSS framework beyond Tailwind, no third-party scripts.

## Consequences

- One language (PHP) and one rendering path to maintain; tests can check the HTML a visitor receives.
- Public pages are complete without JavaScript, which helps search engines, accessibility and slow devices; the public site ships about 3 kB of it.
- Rich client-side behaviour (live previews, drag and drop) would need more JavaScript; it was deliberately left out.
- Developers need to know Blade and Tailwind; there is no component state to manage.
