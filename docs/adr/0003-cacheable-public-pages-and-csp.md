# 0003 — Session-less public pages and a hash-based Content-Security-Policy

Status: accepted (2026-10-25)

## Context

The brief asks for cached public content, a strict security posture and no private data in public caches. A Content-Security-Policy usually allows inline code with a per-request nonce, but a nonce changes the HTML on every request, so no shared cache or ETag could ever match.

## Decision

- Public routes (`routes/site.php`) run without sessions, cookies or CSRF tokens. Only the contact form uses the session-backed `web` group.
- Successful public GET responses are marked `public, max-age=0, s-maxage=…` with an ETag (`CachePublicPages`): a CDN may keep them for `HTTP_CACHE_SECONDS`, browsers always revalidate. Dashboard responses are `private, no-store`.
- The policy (`SecurityHeaders`) allows only the site's own scripts, styles, fonts and images. The only inline code is the generated `@font-face` style block, allowed by its SHA-256 hash (`InlineStyles`); inline scripts, `style` attributes and event handlers are not used anywhere. JSON-LD blocks are data and need no permission.

## Consequences

- Anonymous traffic creates no session rows, and a CDN can serve public pages safely.
- Editors' changes are visible to visitors immediately; behind a CDN, within `HTTP_CACHE_SECONDS`.
- Templates may never use inline `style` or `onclick` attributes; tools that inject inline scripts (for example Laravel Boost's browser logger, disabled in `.env.example`) are blocked by design.
- The Vite development server is allowed only while it is running (`npm run dev`).
