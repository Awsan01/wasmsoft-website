/**
 * Captures full-page screenshots for visual review.
 *
 *   node scripts/screenshot.mjs <url> <output.png> [width=1440] [height=900]
 *
 * Uses the locally installed Google Chrome (no browser download needed).
 */
import { chromium } from '@playwright/test';

const [url, output, width = '1440', height = '900'] = process.argv.slice(2);

if (!url || !output) {
    console.error('Usage: node scripts/screenshot.mjs <url> <output.png> [width] [height]');
    process.exit(1);
}

const browser = await chromium.launch({ channel: 'chrome' });

try {
    const page = await browser.newPage({ viewport: { width: Number(width), height: Number(height) } });
    const errors = [];
    page.on('console', (message) => message.type() === 'error' && errors.push(message.text()));
    page.on('pageerror', (error) => errors.push(error.message));

    const response = await page.goto(url, { waitUntil: 'networkidle' });
    await page.evaluate(() => document.fonts.ready);
    await page.screenshot({ path: output, fullPage: true });

    console.log(`${response?.status()} ${url} -> ${output}`);
    errors.forEach((error) => console.log(`console error: ${error}`));
} finally {
    await browser.close();
}
