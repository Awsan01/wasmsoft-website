# 0007 — Self-hosted fonts through the Vite font pipeline

Status: accepted (2026-10-26)

## Context

The design uses Plus Jakarta Sans for Latin text and IBM Plex Sans Arabic for Arabic. Loading them from a font CDN would send every visitor's address to a third party and require loosening the Content-Security-Policy.

## Decision

- Fonts come from version-pinned `@fontsource` npm packages and are bundled by `laravel-vite-plugin`'s font pipeline (`vite.config.js`): only the needed weights and subsets, `font-display: swap`, and preloading of the two most used Latin weights.
- The `x-ui.fonts` component prints the generated `@font-face` rules inline and registers that block with the Content-Security-Policy by hash. Arabic fonts are only loaded for Arabic text.
- `laravel-vite-plugin` 3.2 writes a separate rule for every font file, so each WOFF2 subset gets a twin WOFF rule with the same descriptors; browsers then download the larger WOFF file as well. `App\Support\FontFaceRules` removes those twins before the block is printed. All browsers the site supports read WOFF2.

## Consequences

- No third-party requests; fonts are cached with the other built assets.
- Measured on a phone profile, removing the WOFF twins cut font downloads by about a third (English home page 109 → 70 kB, Arabic 284 → 200 kB).
- If a future plugin version groups the formats itself, `FontFaceRules` becomes unnecessary (it leaves rules without a WOFF2 twin untouched, so it stays harmless).
- Arabic pages still load four Arabic weights (about 44 kB each). Using fewer weights would save bandwidth at the cost of the typographic design.
