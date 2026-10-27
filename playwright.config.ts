import { defineConfig } from '@playwright/test';

/**
 * Browser tests of the public site and the dashboard (tests/Browser): what
 * visitors and editors see and do in a real browser, in every language.
 *
 *   npm run build && npm run test:browser
 *
 * The tests start their own copy of the site (tests/Browser/server.mjs),
 * with its own database of demo content. BASE=http://127.0.0.1:8000 runs
 * them against a server you started yourself instead: it must hold the
 * demo content and accounts (`php artisan migrate:fresh --seed`, with
 * DASHBOARD_PASSWORD set to their password if you changed
 * DEV_SEED_PASSWORD), the contact form test sends one message through it
 * and the dashboard tests change the dashboard language of the author
 * account while they run.
 *
 * Like the scripts in scripts/, the tests use the locally installed Google
 * Chrome; BROWSER_CHANNEL= (empty) uses Playwright's own Chromium instead
 * (`npx playwright install chromium`).
 */
const port = process.env.BROWSER_TESTS_PORT ?? '8123';
const baseURL = process.env.BASE ?? `http://127.0.0.1:${port}`;
const channel = process.env.BROWSER_CHANNEL ?? 'chrome';

export default defineConfig({
    testDir: 'tests/Browser',
    outputDir: 'test-results',
    fullyParallel: true,
    forbidOnly: Boolean(process.env.CI),
    retries: process.env.CI ? 1 : 0,
    // PHP's development server answers one request at a time on Windows.
    workers: process.platform === 'win32' ? 2 : undefined,
    reporter: process.env.CI ? [['list'], ['html', { open: 'never' }]] : 'list',
    use: {
        baseURL,
        channel: channel === '' ? undefined : channel,
        locale: 'en-GB',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    webServer: process.env.BASE
        ? undefined
        : {
              command: 'node tests/Browser/server.mjs',
              url: `${baseURL}/up`,
              env: { BROWSER_TESTS_PORT: port },
              reuseExistingServer: !process.env.CI,
              timeout: 180_000,
          },
});
