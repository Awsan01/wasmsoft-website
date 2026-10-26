# Project status

State of the Wasmsoft website on 2026-10-26, measured against [the specification](specification.md). Work is on the `feature/complete-website` branch; the public site and the dashboard are complete, tested and documented, but the site has not been deployed and its content is still placeholder text.

## Implemented

**Public site** (Arabic right to left, English, Turkish)
- Home: hero, services, differentiators, figures, selected projects, testimonials, latest news and a call to action. Each section appears only when it has content.
- About (mission and vision text, values, team, milestones), services and service pages, projects with category filter and search, news with categories, tags, related articles and reading time, contact form, and any page editors create.
- Language in every address, language switcher limited to existing translations, no silent fallback; header and footer menus managed in the dashboard; localized error pages for every status code.
- SEO: titles, descriptions, canonical and `hreflang` links, Open Graph, JSON-LD (organization, website, article, breadcrumbs) only where content supports it, XML sitemap, robots.txt (crawlers turned away outside production), `noindex` on the dashboard, errors and previews.

**Dashboard** (modules A–J of the specification)
- Overview with real counts, missing translations, drafts, scheduled items, recent work and recent activity.
- Pages, services, projects, news (with categories and tags): drafts, scheduling, publishing, archiving, previews in each language, featured items, ordering, slugs checked for conflicts per language.
- Home and about page items: highlights (differentiators, values, figures), testimonials (shown only with recorded consent), team members, milestones.
- Media library: JPEG, PNG, WebP and PDF only, re-encoded images with responsive WebP sizes, alternative text per language, usage shown and deletion blocked while in use.
- Navigation: header links, footer columns and links, to fixed pages, editor pages or checked external addresses.
- Contact inquiries: search, filters, statuses, internal notes, spam separated, automatic deletion after the retention period; hidden from authors and viewers.
- Users: invitations by email, four roles, activation and deactivation (signs the person out), protection of the last administrator; own profile and password.
- Settings: company name, tagline and description per language, languages, logo, favicon, sharing image, contact details, alert address, social links, footer note. No secrets or server settings are shown.
- Audit log of sign-ins and every administrative change, with filters.

**Engineering**
- Security: server-side authorization on every route (tested route by route), CSRF, rate limits on sign-in, password reset, invitations, contact form, uploads and the dashboard, strict Content-Security-Policy and security headers, HTTPS-only cookies in production, breached-password check in production. Passwords never reach logs or the audit log, and inquiries appear there only by number; failed sign-ins record the email address that was typed.
- Accessibility: semantic structure, skip links, visible focus, labelled forms with error summaries, reduced-motion support, contrast checked for every colour pair.
- Performance: session-less cacheable public pages, a versioned content cache, eager loading guarded by tests, responsive WebP images, about 3 kB of JavaScript on the public site, self-hosted fonts.
- Documentation: [README](../README.md), [deployment](deployment.md), [backup and restore](backup-and-restore.md), [rollback](rollback.md), [architecture decisions](adr/), a complete `.env.example`.

## Verification results (2026-10-26)

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

## Known limitations

- **Database engines:** tests run on SQLite only. MySQL, MariaDB and PostgreSQL should work (portable migrations and queries) but are untested here; on PostgreSQL, searches are case-sensitive.
- **No undo:** deleting in the dashboard is permanent and there is no revision history; recovery means restoring a backup ([rollback.md](rollback.md#recovering-content)).
- **No two-factor sign-in** for the dashboard; passwords are long, checked against breaches and rate limited.
- **Search:** only the project list and the dashboard lists can be searched; there is no site-wide search.
- **Uploads:** no SVG or GIF (by design, see [ADR 0005](adr/0005-media-uploads.md)); no image cropping or focal point.
- **Previews:** pages, services, projects and articles can be previewed before publishing; home and about page items appear as soon as they are marked visible.
- **Email:** only new inquiries, invitations and password resets send email.
- **Monitoring:** errors go to the log files; no error-tracking service is connected.
- **Accessibility:** checked automatically and with keyboard tests, not yet with screen readers (NVDA, VoiceOver, TalkBack) or by users with disabilities.
- **Performance:** measured on a local development server only; production numbers depend on the host, compression and caching.
- **Dates:** shown in UTC until `DISPLAY_TIMEZONE` is set; Arabic pages use Western digits (configurable in `config/wasmsoft.php`).

## Outstanding work (recommended)

1. Run the test suite against the production database engine and test backups there ([deployment.md](deployment.md#1-what-the-server-needs)).
2. Deploy to a staging server, then run `npm run check:a11y` and `npm run measure:performance` against it.
3. Add continuous integration (tests, static analysis, formatting and the asset build on every push).
4. Test with screen readers in all three languages.
5. Consider two-factor authentication for administrators and an error-tracking service.
6. Consider fewer Arabic font weights if Arabic pages need to be lighter ([ADR 0007](adr/0007-self-hosted-fonts.md)).

## Business information still needed

Nothing below has been invented. Until it is supplied, the site shows clearly marked sample content in development and empty sections in production.

- **Texts in Arabic, English and Turkish:** company description and tagline, the home page hero (its current fallback "Software built around your business" and its introduction were written during development and must be approved or replaced), about page (mission, vision, values), and the introductions of the services, projects, news and contact pages.
- **Brand:** the logo as a vector file (SVG; the site currently uses the supplied JPG), a favicon, a sharing image (1200 × 630 px), and the official spelling of the company name in Arabic and Turkish if it is not written "Wasmsoft".
- **Services:** the real list with descriptions and benefits.
- **Projects:** real projects with images and the clients' permission to show them.
- **People and proof:** team members (with their consent), company milestones, figures that can be verified, and testimonials with written consent.
- **Contact:** email, phone, address, opening hours, map link, the address that should receive inquiry alerts, and social media profiles.
- **Legal:** a privacy policy for the contact form and any other pages required where the company operates (for example under Turkey's KVKK or the EU's GDPR). Create them as pages in the dashboard and link them in the footer menu.
- **Operations:** the domain, hosting, mail provider and time zone.
