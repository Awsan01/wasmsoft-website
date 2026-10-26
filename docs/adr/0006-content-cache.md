# 0006 — A versioned cache for public content

Status: accepted (2026-10-25)

## Context

The navigation, the sitemap and the home page sections read many tables but change rarely. Caching them must never show stale content after an edit, must work on every cache store (the database store has no tags), and must handle articles scheduled for a future date.

## Decision

- `ContentCache` prefixes every key with a version number. Saving or deleting any content model (`FlushesContentCache`) increments the version, so everything cached becomes unreachable at once; old entries expire on their own (`CONTENT_CACHE_TTL`).
- Writes in the dashboard go through `ContentWriter`: one transaction, one audit entry, then the cache refresh.
- Scheduled publication needs no job per item: `wasmsoft:release-scheduled` runs every minute and refreshes the cache when something's publication time has passed since its last run.
- Pages themselves are not cached by the application; HTTP caching (ADR 0003) covers that.

## Consequences

- An edit is visible on the next request, whichever cache store is used.
- Any change empties the whole content cache. With a small site and rare edits this costs a few extra queries, measured at about 20 queries for the home page with an empty cache.
- The scheduler must run in production for scheduled content to appear on lists and in the sitemap on time.
