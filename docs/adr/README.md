# Architecture decision records

Short records of decisions that shape the code and would be costly to change. Each one states the situation, the decision and its consequences. When a decision changes, add a new record that replaces the old one instead of rewriting history.

| # | Decision | Date |
|---|---|---|
| [0001](0001-server-rendered-laravel.md) | A server-rendered Laravel application, without a front-end framework | 2026-10-25 |
| [0002](0002-translations.md) | Translation tables for routable content, no silent language fallback | 2026-10-25 |
| [0003](0003-cacheable-public-pages-and-csp.md) | Session-less public pages and a hash-based Content-Security-Policy | 2026-10-25 |
| [0004](0004-roles-and-permissions.md) | Four fixed roles with permissions defined in code | 2026-10-25 |
| [0005](0005-media-uploads.md) | Uploads limited to re-encoded images and checked PDFs | 2026-10-25 |
| [0006](0006-content-cache.md) | A versioned cache for public content | 2026-10-25 |
| [0007](0007-self-hosted-fonts.md) | Self-hosted fonts through the Vite font pipeline | 2026-10-26 |
