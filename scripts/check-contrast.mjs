/**
 * Verifies WCAG 2.2 contrast for every foreground/background token pair the
 * templates use. Colours are read from resources/css/tokens.css, so changing
 * a token re-checks every pair that depends on it.
 *
 *   npm run check:contrast
 *
 * Exits with status 1 when a pair falls below its threshold:
 *   text   4.5:1 (WCAG 1.4.3, normal-size text)
 *   large  3:1   (WCAG 1.4.3, bold text from 18.66px / regular from 24px)
 *   ui     3:1   (WCAG 1.4.11, borders of controls, focus indicators, icons)
 */
import { readFileSync } from 'node:fs';

const css = readFileSync(new URL('../resources/css/tokens.css', import.meta.url), 'utf8');
const tokens = Object.fromEntries([...css.matchAll(/--color-([\w-]+):\s*(#[0-9a-f]{6})\b/gi)].map(([, name, hex]) => [name, hex]));

const THRESHOLDS = { text: 4.5, large: 3, ui: 3 };

/**
 * [foreground, background, kind, where it is used]
 * A foreground may carry an opacity suffix ("ink/80"); it is blended over
 * the background before measuring, exactly as the browser renders it.
 */
const PAIRS = [
    ['ink', 'surface', 'text', 'body copy'],
    ['ink', 'canvas', 'text', 'dashboard canvas'],
    ['ink', 'lavender', 'text', 'tinted sections'],
    ['ink/80', 'surface', 'text', 'header navigation links'],
    ['ink/85', 'lavender', 'text', 'info alert body'],
    ['muted', 'surface', 'text', 'secondary text'],
    ['muted', 'canvas', 'text', 'table headers, hints on canvas'],
    ['muted', 'lavender', 'text', 'hints on tinted sections'],
    ['muted', 'purple-50', 'text', 'hovered table rows'],
    ['primary', 'surface', 'text', 'brand headings, active links'],
    ['primary', 'lavender', 'text', 'secondary buttons, active nav items'],
    ['secondary', 'surface', 'text', 'links, eyebrows'],
    ['secondary', 'lavender', 'text', 'links on tinted sections'],
    ['white', 'primary', 'text', 'primary buttons'],
    ['white', 'primary-hover', 'text', 'primary buttons (hover)'],
    ['white', 'secondary', 'text', 'secondary accents'],
    ['white', 'error', 'text', 'danger buttons'],
    ['white', 'error-hover', 'text', 'danger buttons (hover)'],
    ['primary', 'white', 'text', 'on-dark buttons'],
    ['success', 'success-soft', 'text', 'success badges'],
    ['warning-ink', 'warning-soft', 'text', 'warning badges'],
    ['error', 'error-soft', 'text', 'error badges'],
    ['error', 'surface', 'text', 'field errors'],
    ['success', 'surface', 'text', 'success text'],
    ['white', 'purple-950', 'text', 'footer headings'],
    ['on-dark-muted', 'purple-950', 'text', 'footer text'],
    ['on-dark-muted', 'logo-start', 'text', 'text on the brand gradient'],
    ['on-dark-muted', 'logo-end', 'text', 'text on the brand gradient'],
    ['white', 'logo-end', 'text', 'headings on the brand gradient'],
    ['border-strong', 'surface', 'ui', 'form control borders'],
    ['border-strong', 'canvas', 'ui', 'disabled controls'],
    ['secondary', 'surface', 'ui', 'focus ring'],
    ['secondary', 'lavender', 'ui', 'focus ring on tinted sections'],
    ['white', 'purple-950', 'ui', 'focus ring on dark surfaces'],
    ['warning', 'warning-soft', 'ui', 'warning icons'],
    ['accent', 'purple-950', 'ui', 'diagonal bar motif on dark surfaces'],
    ['white', 'purple-900', 'text', 'headings on the featured work band'],
    ['on-dark-muted', 'purple-900', 'text', 'text on the featured work band'],
    ['white', 'night', 'text', 'headings on the dark bands, night buttons'],
    ['white/85', 'night', 'text', 'footer links'],
    ['on-dark-muted', 'night', 'text', 'leads on the dark bands, footer text'],
    ['purple-400', 'night', 'text', 'footer column headings'],
    ['purple-300', 'night', 'text', 'eyebrows on the dark bands'],
    ['white', 'night-soft', 'text', 'project titles on the dark band, night buttons (hover)'],
    ['on-dark-muted', 'night-soft', 'text', 'project summaries on the dark band'],
    ['purple-300', 'night-soft', 'text', 'project categories on the dark band'],
    ['white/45', 'night', 'ui', 'previous / next buttons on the dark band'],
    ['white', 'night', 'ui', 'focus ring on the dark bands'],
    ['white', 'violet-deep', 'text', 'headings on the call to action band'],
    ['white', 'violet', 'text', 'headings on the call to action band'],
    ['on-violet', 'violet-deep', 'text', 'text on the call to action band'],
    ['on-violet', 'violet', 'text', 'text on the call to action band'],
    ['white', 'violet', 'ui', 'focus ring on the call to action band'],
];

const channels = (hex) => hex.match(/[0-9a-f]{2}/gi).map((pair) => parseInt(pair, 16));

const luminance = (rgb) => {
    const [r, g, b] = rgb.map((value) => {
        const c = value / 255;
        return c <= 0.04045 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
    });
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
};

const ratio = (a, b) => {
    const [lighter, darker] = [luminance(a), luminance(b)].sort((x, y) => y - x);
    return (lighter + 0.05) / (darker + 0.05);
};

const resolve = (name) => {
    const hex = tokens[name];
    if (!hex) {
        throw new Error(`Unknown colour token "--color-${name}" in scripts/check-contrast.mjs`);
    }
    return channels(hex);
};

let failures = 0;

for (const [foreground, background, kind, usage] of PAIRS) {
    const [fgName, alphaPercent] = foreground.split('/');
    const bg = resolve(background);
    const alpha = alphaPercent === undefined ? 1 : Number(alphaPercent) / 100;
    const fg = resolve(fgName).map((value, i) => Math.round(value * alpha + bg[i] * (1 - alpha)));

    const measured = ratio(fg, bg);
    const required = THRESHOLDS[kind];
    const passed = measured >= required;
    failures += passed ? 0 : 1;

    console.log(
        `${passed ? 'pass' : 'FAIL'}  ${measured.toFixed(2).padStart(5)}:1  (min ${required}:1 ${kind.padEnd(5)})  ${foreground} on ${background}  — ${usage}`,
    );
}

console.log(failures === 0 ? `\nAll ${PAIRS.length} pairs meet WCAG 2.2 AA.` : `\n${failures} pair(s) below WCAG 2.2 AA.`);
process.exit(failures === 0 ? 0 : 1);
