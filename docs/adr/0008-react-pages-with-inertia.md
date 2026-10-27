# 0008 — React pages rendered by Inertia

Status: accepted (2026-10-27). Replaces [0001](0001-server-rendered-laravel.md). The public site moved first (2026-10-27), the dashboard second (2026-10-28).

## Context

The company asked for the website to be built on Laravel, Inertia.js, React, TypeScript, Tailwind CSS, MySQL and Vite, and for a new public design. Routing, validation, authorization, translations, caching and search engine metadata already lived in Laravel and were covered by tests; a separate React application talking to an API would have duplicated all of it.

## Decision

- Laravel keeps routing, controllers, validation, authorization and data. Controllers return Inertia responses: the name of a React page (`resources/js/pages/site/…` or `resources/js/pages/admin/…`) and the data it shows, prepared by presenters (`app/Http/Presenters`). There is no API and no client-side router of our own.
- Pages, layouts and components are React 19 written in strict TypeScript, styled with the existing Tailwind tokens and logical properties, so the same markup serves left-to-right and right-to-left. Interface texts come from `lang/{ar,en,tr}` and are sent once per language and version (`App\Localization\ClientTranslations`).
- The public site and the dashboard are two Inertia apps, each with its own entry (`resources/js/site.tsx`, `resources/js/admin.tsx`), root view (`resources/views/app`) and shared data (`HandleSiteInertiaRequests`, `HandleAdminInertiaRequests`). Visitors never load dashboard code, and a link from one to the other is a normal page load.
- Links within the site and the current language load the next page in place, as do links within the dashboard. Choosing another language of the site loads a full page, because the document's language, direction and fonts change; the dashboard language changes in place.
- Everything search engines and link previews read from `<head>` (title, description, canonical and `hreflang` links, Open Graph, JSON-LD) is built on the server (`App\Seo\HeadElements`) and is in the HTML of every page, whether or not JavaScript runs.
- Server-side rendering of the public pages is built in (`resources/js/ssr.tsx`) and switched on with `INERTIA_SSR_ENABLED`. It is off by default because it needs a Node.js process on the server ([deployment](../deployment.md#server-side-rendering)). The dashboard is not rendered on the server: it is private and never indexed.
- Public pages stay session-less and cacheable ([0003](0003-cacheable-public-pages-and-csp.md)). Responses vary on Inertia's request headers, and the React output uses no inline `style` attributes, inline scripts or injected style sheets, so the Content-Security-Policy did not change.
- Errors a visitor can act on (404, 403, 419, 429) are pages of the site with its header and footer. Server errors and maintenance keep their self-contained Blade pages, which need neither the database nor JavaScript; so do the emails.

In the dashboard:

- Dates, numbers and counted phrases ("3 days ago", "2 drafts") are written on the server in the dashboard language (`AdminPresenter`, with `App\Localization\Formatter`), so they read exactly as before.
- Forms are Inertia forms. They send JSON with the field names the form requests (`app/Http/Requests/Admin`) always validated, so validation and authorization did not change. After a refused submission everything typed stays, with the errors beside the fields and a focused summary above; after a successful one the page is rebuilt from what was saved, since the server may have normalized it (slugs, lists, removed languages).
- Users who may look at an item but not change it see read-only fields rather than disabled ones: the text stays readable and selectable, and the language tabs keep working.
- The image picker reads pages of the media library as JSON (`admin.media.picker`) and uploads through the library's own upload, which answers JSON to such requests. Requests carry Laravel's `XSRF-TOKEN` cookie as a header.
- The messages of the last action ("Saved") are Inertia flash data: shown once, never brought back by the back button. Over HTTPS the page data the browser keeps in its history is encrypted, and signing out clears it.

## Consequences

- A second language (TypeScript) and a build step for every change to a page. `npm run typecheck` checks the types and `npm run test:browser` (Playwright) checks what visitors and editors see and do; the PHP feature tests check which page and data each address sends.
- The public site now loads about 120 kB of compressed JavaScript (React, Inertia and the page), against 3 kB before. Without server-side rendering, the content appears only once that JavaScript has run: on a slow phone connection the first paint comes noticeably later, and crawlers that do not run JavaScript see the `<head>` and the sitemap but not the page text. Turning server-side rendering on removes both drawbacks ([project status](../project-status.md) has the measurements).
- The dashboard loads about the same amount (120–130 kB compressed, against 3 kB for the modules of the Blade version) and no longer works without JavaScript: its forms, lists and dialogs are React components. For a private tool this was accepted with the move to React; the server still validates and authorizes every submission.
- Moving between pages no longer reloads the layout, styles or fonts. Focus moves to the new content and its title is announced, as after a full page load.
- The Blade components and JavaScript modules of the previous version are gone, except the few the error pages need (`resources/views/components/ui`).
