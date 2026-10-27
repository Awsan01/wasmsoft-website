/**
 * Checks the public site and the dashboard against WCAG 2.2 AA with
 * axe-core (at 1280 and at 320 CSS pixels wide), and checks reflow
 * (WCAG 1.4.10: no sideways scrolling of the page at 320 pixels) on every
 * page it finds.
 *
 *   npm run check:a11y
 *   BASE=https://staging.example.com A11Y_EMAIL=… A11Y_PASSWORD=… npm run check:a11y
 *
 * The public site is crawled from each language's home page and the
 * dashboard from its overview (one page per kind of address, e.g. one
 * edit form per module), in English and in Arabic (RTL). The default
 * account is the local development administrator (DevelopmentSeeder).
 * The contact form and the new-user form are also sent empty once, to
 * check how validation errors are presented (nothing is saved; the
 * contact attempt counts towards that form's hourly limit).
 * Uses the locally installed Google Chrome; a full run over the demo
 * content takes about ten minutes (A11Y_MAX_PAGES limits each crawl).
 * Exits with status 1 when a WCAG violation or a reflow failure is found;
 * axe best-practice findings are listed as warnings.
 */
import AxeBuilder from '@axe-core/playwright';
import { chromium } from '@playwright/test';

const base = new URL(process.env.BASE ?? 'http://127.0.0.1:8000');
const email = process.env.A11Y_EMAIL ?? 'administrator@wasmsoft.test';
const password = process.env.A11Y_PASSWORD ?? process.env.DEV_SEED_PASSWORD ?? 'Wasmsoft-Dev-2026';
const maxPages = Number(process.env.A11Y_MAX_PAGES ?? 100);

const LOCALES = ['ar', 'en', 'tr'];
const WCAG_TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22a', 'wcag22aa'];
const DESKTOP = { width: 1280, height: 900 };
const NARROW = { width: 320, height: 800 };
const SKIPPED_PATHS = [/\/_styleguide$/, /^\/storage\//, /\.(xml|txt|pdf|jpe?g|png|webp)$/i, /^\/admin\/logout$/];

const report = { pages: 0, errors: 0, warnings: 0 };

/** Same-origin links on the page that `scope` accepts. */
const linksOn = async (page, scope) =>
    (await page.$$eval('a[href]', (anchors) => anchors.map((anchor) => anchor.href)))
        .map((href) => new URL(href))
        .filter((url) => url.origin === base.origin && scope(url.pathname))
        .filter((url) => !SKIPPED_PATHS.some((pattern) => pattern.test(url.pathname)))
        .map((url) => url.origin + url.pathname + url.search);

/**
 * Pages of the same kind are checked once: query values are ignored
 * (?page=2 and ?page=3), and in the dashboard record ids and languages too
 * (/admin/posts/5/edit and /admin/posts/6/edit).
 */
const keyOf = (href, byShape) => {
    const url = new URL(href);
    const path = byShape ? url.pathname.replace(/\/\d+(?=\/|$)/g, '/{id}').replace(/\/(ar|en|tr)$/, '/{locale}') : url.pathname;

    return `${path}?${[...url.searchParams.keys()].sort().join('&')}`;
};

/** axe findings at the current viewport size, labelled with its width. */
const audit = async (page) => {
    const { violations } = await new AxeBuilder({ page }).withTags([...WCAG_TAGS, 'best-practice']).analyze();
    const width = page.viewportSize()?.width;

    return violations.map((violation) => ({ ...violation, width, isError: violation.tags.some((tag) => WCAG_TAGS.includes(tag)) }));
};

async function checkPage(page, url) {
    await page.setViewportSize(DESKTOP);
    const response = await page.goto(url, { waitUntil: 'networkidle' });

    const { pathname, search } = new URL(url);

    return inspect(page, response?.status() ?? 0, decodeURIComponent(pathname + search));
}

/**
 * Sends a form empty (the browser's own required-field checks are skipped)
 * and checks the page that shows the server's validation errors. These
 * forms always reject an empty submission, so nothing is saved. Forms of
 * the public site and of the dashboard are sent without leaving the page
 * (Inertia), so the check waits for the server's answer to appear in it
 * (`inPlace`); a form that loads a new page is checked once that has loaded.
 */
async function checkErrors(page, url, form, { inPlace = false } = {}) {
    await page.setViewportSize(DESKTOP);
    await page.goto(url, { waitUntil: 'networkidle' });
    const [response] = await Promise.all([
        inPlace
            ? page.waitForResponse((answer) => answer.request().method() === 'POST')
            : page.waitForNavigation({ waitUntil: 'networkidle' }),
        page.$eval(form, (element) => {
            element.noValidate = true;
            element.requestSubmit();
        }),
    ]);
    const label = `${decodeURIComponent(new URL(url).pathname)} (empty form sent)`;

    if (response?.status() === 429) {
        console.log(`skip 429 ${label}: rate limited, try again later`);
        return;
    }

    if (inPlace) {
        await page
            .locator('[aria-invalid="true"]')
            .first()
            .waitFor({ timeout: 10_000 })
            .catch(() => {});
        await page.waitForLoadState('networkidle');
    }

    if ((await page.locator('[aria-invalid="true"]').count()) === 0) {
        report.errors += 1;
        console.log(`FAIL ${response?.status()} ${label}: no field is marked invalid`);
        return;
    }

    await inspect(page, response?.status() ?? 0, label);
}

async function inspect(page, status, label) {
    const findings = await audit(page);

    // The small-screen layout (navigation drawer, scrolling tables) is checked too.
    await page.setViewportSize(NARROW);
    for (const finding of await audit(page)) {
        if (!findings.some((known) => known.id === finding.id)) {
            findings.push(finding);
        }
    }

    const errors = findings.filter((finding) => finding.isError);
    const warnings = findings.filter((finding) => !finding.isError);

    const overflow = await page.evaluate(() => {
        const width = document.documentElement.clientWidth;
        if (document.documentElement.scrollWidth <= width + 1) {
            return [];
        }
        return [...document.querySelectorAll('body *')]
            .filter((element) => {
                const box = element.getBoundingClientRect();
                return box.width > 0 && (box.right > width + 1 || box.left < -1);
            })
            .slice(0, 5)
            .map((element) => element.outerHTML.slice(0, 120));
    });

    report.pages += 1;
    report.errors += errors.length + (overflow.length > 0 ? 1 : 0);
    report.warnings += warnings.length;

    const result = errors.length || overflow.length ? 'FAIL' : warnings.length ? 'warn' : 'ok  ';
    console.log(`${result} ${status} ${label}`);

    for (const violation of [...errors, ...warnings]) {
        console.log(`       ${violation.isError ? 'error' : 'warning'} ${violation.id} (${violation.impact}, ${violation.width} px): ${violation.help}`);
        for (const node of violation.nodes.slice(0, 3)) {
            console.log(`         ${node.target.join(' ')} — ${node.html.slice(0, 140)}`);
        }
    }

    if (overflow.length > 0) {
        console.log('       error reflow: the page scrolls sideways at 320 px; wider than the screen:');
        overflow.forEach((html) => console.log(`         ${html}`));
    }
}

async function crawl(page, start, scope, { byShape = false } = {}) {
    const queue = [...start];
    const seen = new Set(queue.map((url) => keyOf(url, byShape)));
    let visited = 0;

    while (queue.length > 0 && visited < maxPages) {
        const url = queue.shift();
        await checkPage(page, url);
        visited += 1;

        for (const link of await linksOn(page, scope)) {
            const key = keyOf(link, byShape);
            if (!seen.has(key)) {
                seen.add(key);
                queue.push(link);
            }
        }
    }
}

const browser = await chromium.launch({ channel: 'chrome' });

try {
    const context = await browser.newContext();
    const page = await context.newPage();

    for (const locale of LOCALES) {
        console.log(`\nPublic site (${locale})`);
        const missing = new URL(`/${locale}/a11y-check-missing-page`, base).href;
        await crawl(page, [new URL(`/${locale}`, base).href, missing], (path) => path === `/${locale}` || path.startsWith(`/${locale}/`));
    }

    // One contact attempt per run: the form is rate limited per visitor.
    await checkErrors(page, new URL('/ar/contact', base).href, 'form[action$="/contact"]', { inPlace: true });

    console.log('\nSign-in pages');
    await checkPage(page, new URL('/admin/forgot-password', base).href);
    await checkPage(page, new URL('/admin/login', base).href);

    await page.setViewportSize(DESKTOP);
    await page.fill('input[name=email]', email);
    await page.fill('input[name=password]', password);
    await Promise.all([page.waitForURL((url) => !url.pathname.endsWith('/login')), page.click('main form button[type=submit]')]);

    // The dashboard language is saved on the account: put it back afterwards. The
    // language menu in the top bar is the one whose button shows the current language.
    const switchLanguage = async (code) => {
        await page.goto(new URL('/admin', base).href, { waitUntil: 'networkidle' });

        if ((await page.getAttribute('html', 'lang')) === code) {
            return;
        }

        await page.click('header button:has(> span[lang])');
        await page.click(`header button[lang="${code}"]`);
        await page.waitForFunction((lang) => document.documentElement.lang === lang, code).catch(() => {});
        await page.waitForLoadState('networkidle');
    };
    const accountLanguage = await page.getAttribute('html', 'lang');

    for (const locale of ['en', 'ar']) {
        await switchLanguage(locale);
        console.log(`\nDashboard (${locale})`);

        if ((await page.getAttribute('html', 'lang')) !== locale) {
            report.errors += 1;
            console.log(`FAIL the dashboard did not switch to ${locale}`);
        }

        await crawl(page, [new URL('/admin', base).href], (path) => path === '/admin' || path.startsWith('/admin/'), { byShape: true });
        await checkErrors(page, new URL('/admin/users/create', base).href, 'main form', { inPlace: true });
    }

    await switchLanguage(accountLanguage ?? 'en');
} finally {
    await browser.close();
}

console.log(`\n${report.pages} pages checked: ${report.errors} error(s), ${report.warnings} warning(s).`);
process.exit(report.errors === 0 ? 0 : 1);
