# Project status

State of the Wasmsoft website on 2026-10-28, measured against [the specification](specification.md). The public site and the dashboard are complete, tested and documented, but the site has not been deployed and most of its content is still demonstration content. The public site was rebuilt in React with Inertia and given a new design on 2026-10-27 (stage 1), and the dashboard was rebuilt in React with Inertia on 2026-10-28 (stage 2), both below; both are on the main branch. The contact form was then added to the end of the home page, and the public site was redesigned in the style of payoneer.com (both 2026-10-28, below).

## Stage 1: the public site in React and Inertia (2026-10-27)

Requested by the company: the site moves to Laravel, Inertia.js, React, TypeScript, Tailwind CSS, MySQL and Vite, with a new design based on the logo. The decision and its consequences are in [ADR 0008](adr/0008-react-pages-with-inertia.md).

**What changed**
- Every public page is a React 19 page in strict TypeScript (`resources/js/pages/site`), rendered by Inertia 3 from the existing controllers, which now send the page's data instead of HTML. Routing, validation, authorization, translations, the content cache, cacheable responses and the Content-Security-Policy are unchanged; there is no API.
- New design built on the logo's shapes: its two notched blocks and diagonal bar recur in the home page hero, the featured-work band, page headers and dividers, and are mirrored in Arabic. The logo itself is used exactly as supplied. Header with panels for services and projects, a mobile menu, and a saffron accent used only for decoration.
- New page template "Partnerships" (Pages in the dashboard), with partnership models managed as highlights of a new "Partnership" group. The demo content has a sample partnerships page in the three languages, linked in the header and footer.
- Moving between pages of one language happens in place: focus moves to the content and the new title is announced. Choosing another language loads a full page. Errors visitors can act on (404, 403, 419, 429) are pages of the site; server errors and maintenance keep their self-contained pages.
- Titles, descriptions, canonical and language links, Open Graph and JSON-LD are still built on the server and are in the HTML of every page. Server-side rendering of the content is available and off by default ([deployment](deployment.md#server-side-rendering)).
- Tests: the PHP feature tests now check the page and data each address sends instead of its HTML; new browser tests (`tests/Browser`, Playwright) check the rendered pages.
- Removed: the public site's Blade views and components and its JavaScript modules, replaced by the React pages.

**Verification results (2026-10-27)**

| Check | Result |
|---|---|
| `php artisan test` | 448 tests, 10,285 assertions, all passing (SQLite) |
| `vendor/bin/phpstan analyse` (level 8) | No errors |
| `vendor/bin/pint` | Code formatted |
| TypeScript check (`npm run typecheck`, which covers `resources/js`) | No errors; the browser tests and `playwright.config.ts` were checked as well. Run with TypeScript 6 against the type definitions of React 19 and Inertia 3.9.1 taken from their sources, because npm packages could not be installed in that session |
| Browser tests (`npm run test:browser`; Playwright 1.63 with Chromium) | 17 tests passing. Every page reachable from each language's home page (35 per language) answers, is in its language and direction, has one main heading, its landmarks, text alternatives on images, safe new-tab links and no sideways scrolling at 320 px; in-place navigation with focus and announcement, the back button, the skip link, the services panel and the phone menu by keyboard, the language switch, the testimonial tabs in both reading directions, project filters and search, the contact form (errors, confirmation, preselected service) and the 404 page in each language. No test logged a browser error or a Content-Security-Policy violation |
| `npm run check:contrast` | All 38 colour pairs meet WCAG 2.2 AA |
| `npm run check:a11y` | **Not run**: axe-core could not be installed. The script's crawl, reflow check and empty contact form check ran with axe replaced by a stub (25 pages, no failure), which says nothing about WCAG rules |
| `npm run measure:performance`, phone profile (development server, uncompressed) | Without server-side rendering: first paint 3.2–3.8 s, LCP 3.2–4.3 s on 9 pages, all over the 2.5 s budget; CLS 0. With server-side rendering: first paint 1.5–2.4 s, LCP 1.5–2.8 s on 8 pages, 6 within budget; CLS 0. Compression in production shrinks the 370 kB of JavaScript to about 120 kB |
| Server-side rendering | Rendered pages checked: complete content in the HTML, one set of `<head>` elements, no inline `style` attributes, and no warnings when the browser takes over (development build of React) |
| Production build (`npm run build`) | **Not run**: npm packages could not be installed. The assets used for all checks above were built by an equivalent script (esbuild and Tailwind's compiler, same inputs) |
| `composer audit`, `npm audit` | **Not run** (no network access) |

To do on a machine with npm access before committing (the commands that could not run in either session):

```sh
composer install
npm install                 # updates package-lock.json for React, Inertia and TypeScript: commit it
npm run build
npm run typecheck
npm run test:browser
php artisan test --compact
npm run check:a11y          # with the site running and the demo content seeded
composer audit && npm audit
```

## Stage 2: the dashboard in React and Inertia (2026-10-28)

**What changed**
- Every dashboard page is a React page in strict TypeScript (`resources/js/pages/admin`): sign-in, password reset and invitation, the overview, pages, services, projects, news with categories and tags, highlights, testimonials, team, milestones, the media library, navigation, inquiries, users, profile, settings and the audit log. It is a second Inertia app with its own entry, root view and shared data (the brand, languages, signed-in account, sidebar and interface texts). Routes, form requests, policies, the audit log and the content cache did not change.
- The layout, sidebar, top bar, language menu, account menu, navigation drawer, confirmation dialog and messages were rebuilt as React components with the same structure and design as before (the dashboard was not redesigned; it shares the site's colour and type tokens).
- Forms keep everything typed after a refused submission (including unticked tags and removed gallery images) and are rebuilt from the saved data after a successful one. The summary of errors receives focus, and the language tab with errors opens by itself.
- Users with read-only access see read-only fields instead of disabled ones, so they can read and select every language's text and switch the language tabs (in the Blade version the tabs were disabled for them too).
- The image picker reads the library as JSON (`admin.media.picker`) and uploads through the library's upload; searching inside it never sends the form it was opened from. Reordering keeps keyboard focus on the moved row. The dashboard language changes without reloading the page.
- Tests: the dashboard's PHP feature tests check the page and data each address sends (`assertInertia`) instead of its HTML; tests of re-displayed old input became tests of the form's starting values, and keeping typed values after an error is now checked in the browser. New browser tests (`tests/Browser/dashboard.spec.ts`) cover signing in and out, in-place navigation, creating and deleting with confirmation, a refused save, the image picker and gallery by keyboard, reordering, read-only access, the Arabic dashboard and the phone drawer. `scripts/check-accessibility.mjs` signs in, switches language and sends the empty user form through the React dashboard.
- Removed: the dashboard's Blade views and components, its JavaScript modules (`resources/js/admin.js`, `resources/js/modules`), `App\View\FormField`, `App\View\Components\Admin` and the pagination views. Blade remains only for the server error pages and the emails.

**Verification results (2026-10-28)**

| Check | Result |
|---|---|
| `php artisan test` | 448 tests, 10,885 assertions, all passing (SQLite) |
| `vendor/bin/phpstan analyse` (level 8) | No errors |
| `vendor/bin/pint` | Code formatted |
| TypeScript check (`resources/js`) | No errors (TypeScript 6, React 19 and Inertia 3.9.1 type definitions from their sources, as in stage 1) |
| Browser tests (Playwright 1.63 with Chromium) | 26 tests passing: the 17 of the public site and 9 new dashboard tests, with no browser error, failed request or Content-Security-Policy violation |
| Page structure check (one-off browser script, 34 dashboard pages at 1440 and 320 px) | Every field labelled, every button and link named, no duplicate ids or broken `aria-labelledby`, `aria-describedby` and `aria-controls` references, one main heading, no skipped heading levels, no sideways scrolling. It found two problems, both fixed: closed dialogs pointed to titles that only exist while they are open, and the post form scrolled sideways by 5 px at 320 px |
| `npm run check:contrast` | All 38 colour pairs meet WCAG 2.2 AA (the dashboard uses the same tokens) |
| `npm run check:a11y` | **Not run**: axe-core could not be installed. With axe replaced by a stub, the script's crawl ran through the React dashboard: 201 pages (site in 3 languages, 53 kinds of dashboard page in English and in Arabic), sign-in, the language switch and the empty user form all worked, with no reflow failure. This says nothing about WCAG rules |
| JavaScript of a dashboard page (preview build, compressed with gzip) | 119–126 kB, against 3 kB for the Blade version's modules |
| Production build, `composer audit`, `npm audit` | **Not run**, as in stage 1 (no npm or network access) |


## The contact form on the home page (2026-10-28)

Requested by the company: the home page should be a complete landing page, with the contact form on it.

**What changed**
- The home page now ends with the contact form, next to the contact details that have been filled in; it replaces the closing call to action there. Every "Start a project" button on the home page (also in the header and the phone menu) leads to it. Elsewhere they still lead to the contact page, which stays as it was (the menu and the service pages link to it).
- The home page still has no session, so it can still be cached for everyone. The form asks for its spam-guard token when a visitor first moves into it (`/{locale}/contact/token`: starts the session, rate limited, never cached) and is sent as JSON to the same address as the contact page's form, so mistakes and the confirmation appear in place. After an expired form or session it gets a new token at once, and the message can be sent again with what was typed.
- Both pages use one form component (`resources/js/components/site/contact-form.tsx`). On the contact page, a form left open past the token's two hours now uses the new token the page receives instead of being refused again.
- Tests: feature tests for the token address (valid token, session and CSRF cookies, no caching, rate limit), for messages sent as JSON (stored and confirmed, mistakes answered field by field) and for the home page (the form's data, still no cookies and still publicly cacheable); browser tests for sending from the home page (mistakes, confirmation, another message), for an expired form and for "Start a project" in the phone menu. Browser tests can now expect an error answer on purpose (`expectErrorResponse` in `tests/Browser/fixtures.ts`).

**Verification results (2026-10-28)**

| Check | Result |
|---|---|
| `php artisan test` | 453 tests, 10,936 assertions, all passing (SQLite) |
| `vendor/bin/phpstan analyse` (level 8) | No errors |
| `vendor/bin/pint` | Code formatted |
| TypeScript check (`resources/js`, browser tests) | No errors |
| Browser tests (Playwright 1.63 with Chromium) | 29 tests passing, with no browser error, unexpected failed request or Content-Security-Policy violation |
| Page structure check (home and contact pages in the three languages, at 1440 and 320 px) | No issues: fields labelled, controls named, no duplicate ids or broken references, one main heading, no skipped heading levels, no sideways scrolling |
| `npm run check:contrast` | All 38 colour pairs meet WCAG 2.2 AA (no new colours) |
| Production build, `npm run check:a11y`, `composer audit`, `npm audit` | **Not run** in this session (no npm or network access) |

## The Payoneer-style redesign (2026-10-28)

Requested by the company: the public site follows the style of payoneer.com, the projects and services look real, the word "Sample" appears nowhere, and two sections it did not like are replaced: the home page's opening figure (two notched cards and the diagonal bar) and the "Why work with us" section with its figures. The Wasmsoft logo and purple stay; no logo, image or text of Payoneer is used. The dashboard keeps its design.

**What changed**
- **Design system** (`resources/css`): large light headlines (Plus Jakarta Sans 300 is now loaded and preloaded; Arabic headlines use IBM Plex Sans Arabic 400), buttons with round ends on the public site (`ButtonShapeContext` in `components/ui/button.tsx`, set by the site layout only), white rounded panels and cards on lavender bands, dark "night" bands lit by soft purple light, a violet band for calls to action, and larger, rounder form fields on the public site. New tokens: `night`, `night-soft`, `violet-deep`, `violet`, `on-violet`, `orchid`, `blush`; new utilities: `text-section`, `text-title`, `bg-night-glow`, `bg-violet-band`, `bg-art`, `rail`. The motifs of the previous design (split figure, slanted pair, page-header slash, slash dividers, diagonal stripes) were removed.
- **Illustrations drawn in code** (`components/site/art`): one picture per service icon (website, app, assistant, business modules, servers, roadmap), the home page's application, phone and notes, a dashboard with notes repeating the first reasons to choose the company, and a globe of dots. They contain no text to translate except one short note and the site's language codes, and are hidden from assistive technology.
- **Home page**, in order: hero with an email box that carries the address into the contact form and moves there; projects and products on a dark band as a sideways rail with previous / next buttons (`components/site/rail.tsx`; up to six projects, was five); the six services as illustrated cards; "Why choose us" with round icons over the dashboard picture; the figures on a dark band; testimonials, only when real ones exist; the latest news; the contact form on the violet band.
- **Everything else restyled:** header, menu panels, phone menu, language menu, footer (now ending with a large logo lock-up), services (list, and detail with its picture), projects, news, about, partnerships, custom pages, contact, the error pages (also the self-contained server error pages) and the translation-missing page.
- **Demo content rewritten** (`database/seeders`: `DemoContentSeeder`, texts in `DemoContent`, covers drawn by `DemoArtwork`): the company's six services with the summaries it supplied, translated into Arabic and Turkish, plus descriptions and benefits written from them; four example projects that name no client or result; Dhaman and Fantastic as products in development; eight articles; texts for the home, about and partnerships pages; only true figures. Nothing is labelled "Sample" any more, and nothing stands in for facts the company has not given: no testimonials, team, history, phone, address or opening hours (those sections stay hidden). See the README.
- **Translations:** new texts for the home page (email box, why-choose-us and figures introductions) and the rail buttons in the three languages; the home page's fallback headline is now "Software that powers your business growth".
- **Tests:** `DemoContentSeederTest` rewritten (the company's services, nothing marked as a sample in any language, only facts the company gave, drawn covers, re-runs); the home page test now expects six projects; 15 new colour pairs in `npm run check:contrast`.
- **Dashboard dialogs** (found while verifying): the image library, the confirmation and the phone drawer now follow Escape at once (`useModalDialog` in `components/admin/dialog.tsx`). The browser closes a dialog on Escape but sends its close event a moment later, so a dialog opened again within that moment stayed closed, or closed again by itself; the image picker's browser test failed about one run in four because of it.

**To see it on the development machine:** `npm run build` (or keep `composer run dev` running). The old demo content stays in an existing database; `php artisan migrate:fresh --seed` replaces it with the new one, and deletes everything else in the local database too.

**Verification results (2026-10-28)**

| Check | Result |
|---|---|
| `php artisan test` | 455 tests, 11,037 assertions, all passing (SQLite) |
| `vendor/bin/phpstan analyse` (level 8) | No errors |
| `vendor/bin/pint` | Code formatted |
| TypeScript check (`resources/js`, browser tests) | No errors |
| Browser tests (Playwright 1.63 with Chromium) | 30 tests passing, with no browser error, unexpected failed request or Content-Security-Policy violation, including two new ones: the hero's email box (the address reaches the form, which takes the focus) and the projects rail (moves one project at a time, in both reading directions, and its buttons keep the focus). The client quotes test is skipped because the demo content has no quotes; it passed against a copy of the site with two temporary quotes, removed afterwards. The 320 px reflow check now measures the page once it is drawn at that size: right after a resize, lengths in viewport units could still have their desktop values (`tests/Browser/fixtures.ts`; `scripts/check-accessibility.mjs` waits the same way). The image picker test failed 2 of 8 repeated runs before the dialog fix and passed 24 of 24 after it |
| Page structure check (16 pages: the home page in three languages and the services, projects, news, about, partnerships, contact and 404 pages, at 1440 and 320 px) | No issues: fields labelled, controls named, no duplicate ids or broken references, one main heading, no skipped heading levels, no sideways scrolling |
| `npm run check:contrast` | All 53 colour pairs meet WCAG 2.2 AA |
| Visual review (screenshots) | The home page in English and Arabic at 1440 px and in English at 390 px; the services, projects, news, about, partnerships, contact and 404 pages in English at 1440 px, five of them at 390 px and three in Arabic; the menu panels, the phone menu, the language menu, the email box's focus and the client quotes |
| Production build, `npm run check:a11y`, `npm run measure:performance`, `composer audit`, `npm audit` | **Not run** in this session (no npm or network access); the assets for the checks above were built by the equivalent script used since stage 1 |

## Implemented

**Public site** (Arabic right to left, English, Turkish)
- Home: hero with an email box leading to the contact form, projects and products, services, reasons to choose the company, figures, testimonials and latest news, each shown only when it has content, then the contact form with the contact details that have been filled in.
- About (mission and vision text, values, team, milestones), partnerships (partnership models and a call to action), services and service pages, projects with category filter and search, news with categories, tags, related articles and reading time, contact form, and any page editors create.
- Language in every address, language switcher limited to existing translations, no silent fallback; header and footer menus managed in the dashboard; localized error pages for every status code.
- SEO: titles, descriptions, canonical and `hreflang` links, Open Graph, JSON-LD (organization, website, article, breadcrumbs) only where content supports it, XML sitemap, robots.txt (crawlers turned away outside production), `noindex` on the dashboard, errors and previews.

**Dashboard** (modules A–J of the specification)
- Overview with real counts, missing translations, drafts, scheduled items, recent work and recent activity.
- Pages, services, projects, news (with categories and tags): drafts, scheduling, publishing, archiving, previews in each language, featured items, ordering, slugs checked for conflicts per language.
- Home, about and partnerships page items: highlights (differentiators, values, figures, partnership models), testimonials (shown only with recorded consent), team members, milestones.
- Media library: JPEG, PNG, WebP and PDF only, re-encoded images with responsive WebP sizes, alternative text per language, usage shown and deletion blocked while in use.
- Navigation: header links, footer columns and links, to fixed pages, editor pages or checked external addresses.
- Contact inquiries: search, filters, statuses, internal notes, spam separated, automatic deletion after the retention period; hidden from authors and viewers.
- Users: invitations by email, four roles, activation and deactivation (signs the person out), protection of the last administrator; own profile and password.
- Settings: company name, tagline and description per language, languages, logo, favicon, sharing image, contact details, alert address, social links, footer note. No secrets or server settings are shown.
- Audit log of sign-ins and every administrative change, with filters.

**Engineering**
- Security: server-side authorization on every route (tested route by route), CSRF, rate limits on sign-in, password reset, invitations, contact form, uploads and the dashboard, strict Content-Security-Policy and security headers, HTTPS-only cookies in production, breached-password check in production. Passwords never reach logs or the audit log, and inquiries appear there only by number; failed sign-ins record the email address that was typed.
- Accessibility: semantic structure, skip links, visible focus, labelled forms with error summaries, reduced-motion support, contrast checked for every colour pair.
- Performance: session-less cacheable public pages, a versioned content cache, eager loading guarded by tests, responsive WebP images, self-hosted fonts. The public site and the dashboard each load about 120 kB of compressed JavaScript (React and Inertia, shared by all their pages); server-side rendering of the public site is optional.
- Documentation: [README](../README.md), [deployment](deployment.md), [backup and restore](backup-and-restore.md), [rollback](rollback.md), [architecture decisions](adr/), a complete `.env.example`.

## Verification results of the Blade version (2026-10-26)

These results describe the site before stage 1; the public site's results of 2026-10-27 are above.

| Check | Result |
|---|---|
| `php artisan test` | 438 tests, 8,601 assertions, all passing (SQLite) |
| `vendor/bin/phpstan analyse` (level 8) | No errors |
| `vendor/bin/pint` | Code formatted |
| `npm run check:contrast` | All 35 colour pairs meet WCAG 2.2 AA |
| `npm run check:a11y` on the demo content | 166 pages (site in 3 languages, dashboard in 2): no axe-core violation or warning at 1280 and 320 px, no sideways scrolling at 320 px |
| Keyboard check (one-off browser script) | Every focusable element shows a focus indicator; the mobile menu opens, moves focus, closes with Escape and returns focus |
| `npm run measure:performance` (development server, uncompressed) | Desktop: LCP 130–460 ms and CLS 0 on all 9 pages measured. Phone profile (slow 4G, 4× slower CPU): CLS 0 everywhere, LCP 1.3–3.8 s on 8 pages and 6.6 s on the contact page, where the development server took 5.6 s to answer. The development server neither compresses nor runs in parallel well, so these phone figures are pessimistic |
| Server response time (development server, warm cache) | 26–44 ms for public pages |
| `composer audit`, `npm audit` | No known vulnerabilities |
| Backup, restore and migration rollback | Verified on SQLite ([details](backup-and-restore.md#what-has-been-verified)) |
| Fresh clone (acceptance) | `composer run setup`, `php artisan db:seed` and `php artisan storage:link` as in the README, then all 438 tests passed in the clone, every public page, the sitemap, robots.txt, an uploaded image and the dashboard (signed in as the demo administrator) answered correctly with no browser errors |

## Changes after that verification (2026-10-26, evening)

Verified on 2026-10-27: the whole PHP test suite passes, including `DemoContentSeederTest`, and the browser tests load a fresh database with this seeder on every run.

- **Sample content in every section** (requested by the company, to preview the full site): `DemoContentSeeder` now also seeds testimonials, figures, team members, milestones, the tagline, description and footer note, contact details and social links, and brings services, projects and published articles to two full rows each. Every sample stays labelled as one (see the README).
- **Dhaman and Fantastic**, the company's products in development, appear in the portfolio by name with a placeholder description, as the newest projects.
- **Safe to re-run:** the seeder now only adds what each section is short of, so it can fill an existing local database without changing or deleting its content or saved settings (previously it did nothing once services or articles existed).
- **Tests:** `tests/Feature/Seeders/DemoContentSeederTest.php` updated. The test asserting that testimonials, team, history and figures are never seeded was replaced by one asserting that every such sample is labelled in all three languages; new tests cover the re-run and existing-content cases.

To fill an existing local database with the samples: `php artisan db:seed --class=Database\\Seeders\\DemoContentSeeder`.

## Known limitations

- **Database engines:** tests run on SQLite only. MySQL, MariaDB and PostgreSQL should work (portable migrations and queries) but are untested here; on PostgreSQL, searches are case-sensitive.
- **No undo:** deleting in the dashboard is permanent and there is no revision history; recovery means restoring a backup ([rollback.md](rollback.md#recovering-content)).
- **No two-factor sign-in** for the dashboard; passwords are long, checked against breaches and rate limited.
- **Search:** only the project list and the dashboard lists can be searched; there is no site-wide search.
- **Uploads:** no SVG or GIF (by design, see [ADR 0005](adr/0005-media-uploads.md)); no image cropping or focal point.
- **Previews:** pages, services, projects and articles can be previewed before publishing; home and about page items appear as soon as they are marked visible.
- **Email:** only new inquiries, invitations and password resets send email.
- **Monitoring:** errors go to the log files; no error-tracking service is connected.
- **JavaScript:** without server-side rendering, the public pages show their content only once their JavaScript has run (their `<head>` metadata and the sitemap do not depend on it).
- **Dashboard:** needs JavaScript (its pages are React components); it kept the previous design and was not redesigned with the public site.
- **Browsers:** the browser tests and checks run in Chrome (Chromium) only.
- **Accessibility:** checked automatically and with keyboard tests, not yet with screen readers (NVDA, VoiceOver, TalkBack) or by users with disabilities.
- **Performance:** measured on a local development server only; production numbers depend on the host, compression and caching.
- **Dates:** shown in UTC until `DISPLAY_TIMEZONE` is set; Arabic pages use Western digits (configurable in `config/wasmsoft.php`).

## Outstanding work (recommended)

1. Finish the checks that could not run in the sessions of stages 1 and 2 (above). On the development machine `npm run build` has completed and `npm run test:browser` has passed (2026-10-28, before the redesign); after the redesign, run both again, and report `php artisan test`, `npm run check:a11y` (the WCAG rules), `composer audit` and `npm audit`.
2. Decide whether production runs server-side rendering (recommended, needs Node.js on the server).
3. Run the test suite against the production database engine and test backups there ([deployment.md](deployment.md#1-what-the-server-needs)).
4. Deploy to a staging server, then run `npm run check:a11y` and `npm run measure:performance` against it.
5. Add continuous integration (tests, static analysis, formatting, the type check, the asset build and the browser tests on every push).
6. Test with screen readers in all three languages.
7. Consider two-factor authentication for administrators and an error-tracking service.
8. Consider fewer Arabic font weights if Arabic pages need to be lighter ([ADR 0007](adr/0007-self-hosted-fonts.md)).

## Business information still needed

Nothing below has been invented. Until it is supplied, the demonstration content in development stays limited to what the README describes, and these sections stay empty in production.

- **Texts in Arabic, English and Turkish:** company description and tagline, the home page hero (its fallback "Software that powers your business growth" and the other home page texts were written during development and must be approved or replaced), about page (mission, vision, values), and the introductions of the services, projects, news and contact pages. The Arabic and Turkish translations should be checked by native speakers.
- **Brand:** the logo as a vector file (SVG; the site currently uses the supplied JPG), a favicon, a sharing image (1200 × 630 px), and the official spelling of the company name in Arabic and Turkish if it is not written "Wasmsoft".
- **Services:** supplied by the company on 2026-10-28 (six services with their summaries). Still to approve: the longer descriptions and benefits written from those summaries in the demo content.
- **Partnerships:** the partnership models the company offers and, with their permission, its partners (the demo content describes three ways to partner, to be approved).
- **Projects:** real projects with images and the clients' permission to show them (the demo content has four examples that name no client), and what Dhaman and Fantastic do.
- **People and proof:** team members (with their consent), company milestones, figures that can be verified, and testimonials with written consent.
- **Contact:** email, phone, address, opening hours, map link, the address that should receive inquiry alerts, and social media profiles.
- **Legal:** a privacy policy for the contact form and any other pages required where the company operates (for example under Turkey's KVKK or the EU's GDPR). Create them as pages in the dashboard and link them in the footer menu.
- **Operations:** the domain, hosting, mail provider and time zone.
