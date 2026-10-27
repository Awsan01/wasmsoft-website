/**
 * Measures how fast public pages load in Chrome: time to first byte,
 * first and largest contentful paint (FCP, LCP), cumulative layout shift
 * (CLS), and the number and size of downloads.
 *
 *   npm run measure:performance
 *   BASE=https://staging.example.com npm run measure:performance -- /en /ar/news
 *
 * Each page loads in a fresh browser profile (nothing cached) as a phone on
 * a slow 4G connection with a 4x slower CPU — the conditions of
 * Lighthouse's mobile profile — unless PERF_DESKTOP=1 is set. Without page
 * arguments it measures each language's home page, the English list pages
 * and the first project and article it finds. Results are compared with
 * the "good" Core Web Vitals thresholds (LCP 2.5 s, CLS 0.1); the script
 * exits with status 1 when a page is over them.
 *
 * Numbers from `php artisan serve` (a single-process development server)
 * are pessimistic for time to first byte; measure the production stack
 * for the real figures.
 */
import { chromium } from '@playwright/test';

const base = new URL(process.env.BASE ?? 'http://127.0.0.1:8000');
const desktop = process.env.PERF_DESKTOP === '1';
const BUDGET = { lcp: 2500, cls: 0.1 };

const SLOW_4G = { offline: false, latency: 150, downloadThroughput: (1.6 * 1024 * 1024) / 8, uploadThroughput: (750 * 1024) / 8 };

const browser = await chromium.launch({ channel: 'chrome' });

async function open(path) {
    const context = await browser.newContext(
        desktop ? { viewport: { width: 1440, height: 900 } } : { viewport: { width: 390, height: 844 }, deviceScaleFactor: 3, isMobile: true, hasTouch: true },
    );
    const page = await context.newPage();

    if (!desktop) {
        const cdp = await context.newCDPSession(page);
        await cdp.send('Network.emulateNetworkConditions', SLOW_4G);
        await cdp.send('Emulation.setCPUThrottlingRate', { rate: 4 });
    }

    // Collect paint and layout-shift entries from the very start of the load.
    await page.addInitScript(() => {
        window.__perf = { lcp: 0, cls: 0 };
        new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                window.__perf.lcp = entry.startTime;
            }
        }).observe({ type: 'largest-contentful-paint', buffered: true });
        new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (!entry.hadRecentInput) {
                    window.__perf.cls += entry.value;
                }
            }
        }).observe({ type: 'layout-shift', buffered: true });
    });

    const response = await page.goto(new URL(path, base).href, { waitUntil: 'networkidle' });
    // Painting can finish after the network goes quiet on a throttled CPU.
    await page.waitForFunction(() => performance.getEntriesByName('first-contentful-paint').length > 0, null, { timeout: 30_000 });
    await page.waitForTimeout(1000);

    return { context, page, status: response?.status() ?? 0, compressed: Boolean(response?.headers()['content-encoding']) };
}

async function measure(path) {
    const { context, page, status, compressed } = await open(path);

    const metrics = await page.evaluate(() => {
        const [navigation] = performance.getEntriesByType('navigation');
        const fcp = performance.getEntriesByName('first-contentful-paint')[0]?.startTime ?? 0;
        const kinds = { html: 0, css: 0, js: 0, font: 0, image: 0, other: 0 };
        const add = (url, bytes) => {
            const kind = /\.css(\?|$)/.test(url)
                ? 'css'
                : /\.m?js(\?|$)/.test(url)
                  ? 'js'
                  : /\.(woff2?|ttf|otf)(\?|$)/.test(url)
                    ? 'font'
                    : /\.(avif|webp|jpe?g|png|gif|svg|ico)(\?|$)/.test(url)
                      ? 'image'
                      : 'other';
            kinds[kind] += bytes;
        };
        const resources = performance.getEntriesByType('resource');
        kinds.html = navigation.transferSize;
        resources.forEach((entry) => add(entry.name, entry.transferSize));

        return {
            ttfb: navigation.responseStart,
            fcp,
            lcp: window.__perf.lcp,
            cls: window.__perf.cls,
            requests: resources.length + 1,
            kinds,
        };
    });

    await context.close();

    return { path, status, compressed, ...metrics };
}

/** The first link on `listPath` to a single item under `prefix` (not a category or tag list). */
async function firstLink(listPath, prefix) {
    const { context, page } = await open(listPath);
    const href = await page.$$eval(
        'a[href]',
        (anchors, start) => anchors.map((a) => new URL(a.href).pathname).find((p) => p.startsWith(start) && p !== start && !/\/(category|tag)\//.test(p)),
        prefix,
    );
    await context.close();

    return href ? decodeURI(href) : null;
}

const kb = (bytes) => `${(bytes / 1024).toFixed(1)} kB`;
const ms = (value) => `${Math.round(value)} ms`;

try {
    let paths = process.argv.slice(2);

    if (paths.length === 0) {
        paths = ['/ar', '/en', '/tr', '/en/services', '/en/projects', '/en/news', '/en/contact'];
        for (const [list, prefix] of [['/en/projects', '/en/projects/'], ['/en/news', '/en/news/']]) {
            const detail = await firstLink(list, prefix);
            if (detail) {
                paths.push(detail);
            }
        }
    }

    console.log(`${desktop ? 'Desktop, no throttling' : 'Phone, slow 4G, 4x CPU slowdown'} — ${base.origin}\n`);
    let over = 0;
    let uncompressed = 0;

    for (const path of paths) {
        const result = await measure(path);
        const total = Object.values(result.kinds).reduce((sum, bytes) => sum + bytes, 0);
        const withinBudget = result.lcp <= BUDGET.lcp && result.cls <= BUDGET.cls;
        over += withinBudget ? 0 : 1;
        uncompressed += result.compressed ? 0 : 1;

        console.log(`${withinBudget ? 'ok  ' : 'SLOW'} ${result.status} ${result.path}`);
        console.log(`       TTFB ${ms(result.ttfb)} · FCP ${ms(result.fcp)} · LCP ${ms(result.lcp)} · CLS ${result.cls.toFixed(3)}`);
        console.log(
            `       ${result.requests} requests, ${kb(total)} transferred (HTML ${kb(result.kinds.html)}, CSS ${kb(result.kinds.css)}, JS ${kb(result.kinds.js)}, fonts ${kb(result.kinds.font)}, images ${kb(result.kinds.image)}, other ${kb(result.kinds.other)})`,
        );
    }

    console.log(over === 0 ? `\nAll ${paths.length} pages are within LCP ${BUDGET.lcp} ms and CLS ${BUDGET.cls}.` : `\n${over} page(s) over budget.`);

    if (uncompressed > 0) {
        console.log('Responses were not compressed (php artisan serve never compresses). Production must serve gzip or brotli, which shrinks HTML and CSS by about 80 %.');
    }

    process.exitCode = over === 0 ? 0 : 1;
} finally {
    await browser.close();
}
