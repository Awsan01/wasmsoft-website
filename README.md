# Wasmsoft website

The public website of Wasmsoft and the dashboard its staff use to manage it.

- **Public site** in Arabic (right to left), English and Turkish: home, about, services, projects, news, contact and any other page editors create.
- **Dashboard** at `/admin`: overview, pages, services, projects, news (categories, tags), home and about page items (highlights, testimonials, team, milestones), media library, navigation menus, contact inquiries, users and roles, settings and an audit log.

The full brief is in [docs/specification.md](docs/specification.md). What is finished, what is not and what the company still has to supply is in [docs/project-status.md](docs/project-status.md).

## Technology

Laravel 13 (PHP 8.4) with Blade templates, Tailwind CSS 4 and a few small JavaScript modules built by Vite 8. There is no front-end framework and no third-party script, font or tracking service: fonts are self-hosted and the Content-Security-Policy only allows the site's own files. Decisions that shape the code are recorded in [docs/adr](docs/adr/).

## Requirements

- PHP 8.4 (Composer allows 8.3; development and tests use 8.4) with the extensions `gd` (with JPEG, PNG and WebP support), `intl`, `mbstring`, `fileinfo`, `openssl`, `pdo_sqlite` (development) or the driver of the production database, and optionally `exif` (corrects photo orientation).
- Composer 2.
- Node.js 22 LTS and npm (assets are built with Vite).
- Google Chrome, only for the browser checks (`npm run check:a11y`, `npm run measure:performance`).

## Local development

```sh
composer run setup     # dependencies, .env, application key, SQLite database, migrations, asset build
php artisan db:seed    # demo accounts and clearly marked sample content
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

The sample content fills every section of the site, so it can be previewed in all three languages: services, projects, news, differentiators, figures, testimonials, values, team, history, contact details and social links. Nothing passes for real company information: every sample text starts with "Sample:" (نموذج: / Örnek:), sample people and companies are called "Sample person" or "Sample client", the contact email uses the reserved `.test` domain, social links point to the networks' home pages and sample images are labelled. Dhaman and Fantastic, the company's products in development, are listed by name with a placeholder description. The seeders refuse to run outside local environments.

On a database that already has content, `php artisan db:seed --class=Database\\Seeders\\DemoContentSeeder` adds only the samples each section is short of. It never changes or removes existing content, and never overwrites a setting that has been filled in.

Built assets are served from `public/build`; after changing views, CSS or JavaScript without `composer run dev`, run `npm run build`.

## Checks

| Command | What it does |
|---|---|
| `php artisan test --compact` | Feature and unit tests (PHPUnit), on an in-memory SQLite database |
| `vendor/bin/pint` | Formats PHP code (Laravel style) |
| `vendor/bin/phpstan analyse` | Static analysis (Larastan, level 8) |
| `npm run check:contrast` | WCAG 2.2 contrast of every colour pair the design tokens use |
| `npm run check:a11y` | Crawls the site and dashboard with axe-core (WCAG 2.2 AA) and checks reflow at 320 px |
| `npm run measure:performance` | Time to first byte, FCP, LCP, CLS and page weight in Chrome |
| `composer audit` / `npm audit` | Known vulnerabilities in dependencies |

The two browser checks need a running site (`BASE=http://127.0.0.1:8000` by default) and, for the dashboard part of `check:a11y`, the demo administrator or the account in `A11Y_EMAIL` / `A11Y_PASSWORD`.

## How the code is organised

| Path | Contents |
|---|---|
| `routes/site.php`, `routes/web.php`, `routes/admin.php` | Public pages (no session), the contact form (session) and the dashboard |
| `app/Http/Controllers/Site`, `app/Http/Controllers/Admin` | Thin controllers; validation in `app/Http/Requests`, authorization in `app/Policies` |
| `app/Auth` | Roles and permissions (`Role`, `Permission`), invitations |
| `app/Content` | Saving content (`ContentWriter`: transaction, audit entry, cache refresh), the public content cache, slugs, Markdown |
| `app/Models` | Eloquent models; translations live in `*_translations` tables (`HasTranslations`) |
| `app/Localization`, `lang/{ar,en,tr}` | Supported languages, formatting, interface texts |
| `app/Media` | Uploads: type checks, generated names, re-encoded images and WebP sizes |
| `app/Seo`, `app/Navigation`, `app/Settings`, `app/Security`, `app/Audit` | Metadata and sitemap, menus, site settings, security headers and spam checks, audit log |
| `resources/css/tokens.css` | Design tokens (colours, type, spacing) used by every component |
| `resources/views/components/{ui,site,admin}` | Shared, public-site and dashboard components |
| `scripts/` | The browser checks above |

## Languages

Every public address starts with the language: `/ar/...`, `/en/...`, `/tr/...`. Content is written separately for each language and is never shown in another language by accident: a page that exists only in English is not listed on the Turkish site, and opening it in Turkish explains that it is not available and links to the languages it exists in. Which languages are offered, and the default, are chosen in the dashboard (Settings). Each dashboard user picks their own dashboard language.

## Further documentation

- [Deployment](docs/deployment.md): production configuration, first installation, updates, scheduler and queue, checklist.
- [Backup and restore](docs/backup-and-restore.md)
- [Rollback](docs/rollback.md)
- [Architecture decisions](docs/adr/)
- [Project status](docs/project-status.md): features, known limitations, outstanding work and missing business information.
