# Wasmsoft website

The public website of Wasmsoft and the dashboard its staff use to manage it.

- **Public site** in Arabic (right to left), English and Turkish: home, about, services, projects, news, contact and any other page editors create. The contact form is also at the end of the home page.
- **Dashboard** at `/admin`: overview, pages, services, projects, news (categories, tags), home and about page items (highlights, testimonials, team, milestones), media library, navigation menus, contact inquiries, users and roles, settings and an audit log.

The full brief is in [docs/specification.md](docs/specification.md). What is finished, what is not and what the company still has to supply is in [docs/project-status.md](docs/project-status.md).

## Technology

Laravel 13 (PHP 8.4) and Inertia.js 3: the pages of the public site and of the dashboard are React 19 components written in TypeScript, styled with Tailwind CSS 4 and built by Vite 8, while Laravel keeps the routing, validation, authorization and data (there is no separate API). There is no third-party script, font or tracking service: fonts are self-hosted and the Content-Security-Policy only allows the site's own files. Decisions that shape the code are recorded in [docs/adr](docs/adr/), the move to React in [ADR 0008](docs/adr/0008-react-pages-with-inertia.md).

## Requirements

- PHP 8.4 (Composer allows 8.3; development and tests use 8.4) with the extensions `gd` (with JPEG, PNG and WebP support), `intl`, `mbstring`, `fileinfo`, `openssl`, `pdo_sqlite` (development) or the driver of the production database, and optionally `exif` (corrects photo orientation).
- Composer 2.
- Node.js 22 LTS and npm (assets are built with Vite; on the server only if server-side rendering is turned on).
- Google Chrome, only for the browser tests and checks (`npm run test:browser`, `npm run check:a11y`, `npm run measure:performance`).

## Local development

```sh
composer run setup     # dependencies, .env, application key, SQLite database, migrations, asset build
php artisan db:seed    # demo accounts and demonstration content
php artisan storage:link
composer run dev       # web server, queue worker and Vite (plus the log viewer on macOS and Linux)
```

Open http://localhost:8000 (the root redirects to the visitor's language) and the dashboard at http://localhost:8000/admin.

The seeder creates one account per role, all with the password in `DEV_SEED_PASSWORD` (default `Wasmsoft-Dev-2026`):

| Account | Role |
|---|---|
| administrator@wasmsoft.test | Administrator: everything, including users, settings and the audit log |
| editor@wasmsoft.test | Editor: all content, media, navigation and inquiries |
| author@wasmsoft.test | Author: writes news articles (own drafts) and uploads media |
| viewer@wasmsoft.test | Viewer: reads content in the dashboard, changes nothing |

The demonstration content fills every page in all three languages, so the site can be seen as it will look:

- **Services:** the company's six services, with the texts the company supplied, translated into Arabic and Turkish.
- **Projects and news:** four example projects and eight articles (six published, one scheduled, one draft), with drawn cover images. They show the kind of content the site is made for and name no client, partner or result. **They exist only in local databases: on the live site, enter the company's real projects and articles in the dashboard.**
- **Products:** Dhaman and Fantastic, listed by name and described as in development. What they do is left for the company to write.
- **Home, about and partnerships pages:** their texts, the reasons to choose the company, its values and the ways to partner.
- **Figures:** only true ones (6 services, 3 languages, 2 products in development).

Nothing stands in for facts the company has not given. There are no testimonials, team members, history, phone number, address or opening hours: those sections stay hidden until they are entered in the dashboard. The contact email uses the reserved `.test` domain and the social links point to the networks' home pages until the real ones are entered in Settings. The seeders refuse to run outside local environments. The texts are in `database/seeders/DemoContent.php` and the cover images are drawn by `database/seeders/DemoArtwork.php`.

On a database that already has content, `php artisan db:seed --class=Database\\Seeders\\DemoContentSeeder` adds only what each section is short of. It never changes or removes existing content, and never overwrites a setting that has been filled in. To replace older demo content with this version, start from an empty local database: `php artisan migrate:fresh --seed`. That command deletes everything in the local database, including anything entered in the dashboard.

Built assets are served from `public/build`; after changing pages, views, CSS or JavaScript without `composer run dev`, run `npm run build` (it also builds the server-side rendering bundle in `bootstrap/ssr`).

An existing copy needs `composer install`, `npm install` and `npm run build` once after updating to the React pages, which brought new packages (`inertiajs/inertia-laravel`, React, TypeScript). Commit the updated `package-lock.json` that `npm install` writes.

## Checks

| Command | What it does |
|---|---|
| `php artisan test --compact` | Feature and unit tests (PHPUnit), on an in-memory SQLite database |
| `vendor/bin/pint` | Formats PHP code (Laravel style) |
| `vendor/bin/phpstan analyse` | Static analysis (Larastan, level 8) |
| `npm run typecheck` | TypeScript check of the React pages and components (strict) |
| `npm run test:browser` | Browser tests (Playwright, `tests/Browser`): every public page in each language, navigation, keyboard use, menus, language switch, contact form, error pages. They start their own copy of the site with its own database of demo content; run `npm run build` first |
| `npm run check:contrast` | WCAG 2.2 contrast of every colour pair the design tokens use |
| `npm run check:a11y` | Crawls the site and dashboard with axe-core (WCAG 2.2 AA) and checks reflow at 320 px |
| `npm run measure:performance` | Time to first byte, FCP, LCP, CLS and page weight in Chrome |
| `composer audit` / `npm audit` | Known vulnerabilities in dependencies |

The two browser checks need a running site (`BASE=http://127.0.0.1:8000` by default) and, for the dashboard part of `check:a11y`, the demo administrator or the account in `A11Y_EMAIL` / `A11Y_PASSWORD`. The browser tests need no running site: they serve one on port 8123 with a database, storage folder and caches of their own in the system's temporary folder, so the development database is never touched (see `playwright.config.ts` for running them against another server).

## How the code is organised

| Path | Contents |
|---|---|
| `routes/site.php`, `routes/web.php`, `routes/admin.php` | Public pages (no session), the contact form (session) and the dashboard |
| `app/Http/Controllers/Site`, `app/Http/Controllers/Admin` | Thin controllers; validation in `app/Http/Requests`, authorization in `app/Policies` |
| `app/Http/Presenters`, `app/Http/Middleware/Handle{Site,Admin}InertiaRequests.php` | The data each page receives (`SitePresenter`, `AdminPresenter`), and what every page of the site or the dashboard shares (language, menus or sidebar, signed-in account, interface texts) |
| `app/Auth` | Roles and permissions (`Role`, `Permission`), invitations |
| `app/Content` | Saving content (`ContentWriter`: transaction, audit entry, cache refresh), the public content cache, slugs, Markdown |
| `app/Models` | Eloquent models; translations live in `*_translations` tables (`HasTranslations`) |
| `app/Localization`, `lang/{ar,en,tr}` | Supported languages, formatting, interface texts |
| `app/Media` | Uploads: type checks, generated names, re-encoded images and WebP sizes |
| `app/Seo`, `app/Navigation`, `app/Settings`, `app/Security`, `app/Audit` | Metadata and sitemap, menus, site settings, security headers and spam checks, audit log |
| `resources/css/tokens.css` | Design tokens (colours, type, spacing) used by every component |
| `resources/js/pages/{site,admin}`, `resources/js/components/{ui,site,admin}`, `resources/js/layouts` | The public site and the dashboard in React and TypeScript: one file per page, shared components (form fields, the translation editor, the image picker…) and the layouts around every page |
| `resources/js/site.tsx`, `resources/js/admin.tsx`, `resources/js/ssr.tsx`, `resources/views/app` | Start of the public site and of the dashboard in the browser, server-side rendering of the site, and the HTML pages they start from |
| `resources/views/errors`, `resources/views/mail`, `resources/views/components/ui` | Blade, still: the self-contained server error pages and the emails |
| `tests/Browser` | Browser tests (Playwright) and the server they start |
| `scripts/` | The browser checks above, the contrast check and the icon generator (`npm run icons`) |

## Languages

Every public address starts with the language: `/ar/...`, `/en/...`, `/tr/...`. Content is written separately for each language and is never shown in another language by accident: a page that exists only in English is not listed on the Turkish site, and opening it in Turkish explains that it is not available and links to the languages it exists in. Which languages are offered, and the default, are chosen in the dashboard (Settings). Each dashboard user picks their own dashboard language.

## Further documentation

- [Deployment](docs/deployment.md): production configuration, first installation, updates, scheduler and queue, checklist.
- [Backup and restore](docs/backup-and-restore.md)
- [Rollback](docs/rollback.md)
- [Architecture decisions](docs/adr/)
- [Project status](docs/project-status.md): features, known limitations, outstanding work and missing business information.
