import { DESKTOP, expect, LOCALES, overflowingElements, test } from './fixtures';

/** Pages checked per language at most (BROWSER_TESTS_MAX_PAGES). */
const MAX_PAGES = Number(process.env.BROWSER_TESTS_MAX_PAGES ?? 80);

/** Addresses that are not pages of the public site: files, assets, the dashboard. */
const NOT_A_PAGE = /^\/(storage|build|admin)(\/|$)|\.[a-z0-9]{2,5}$/i;

// Decorative motion is left out, so that what is measured is the layout itself.
test.use({ viewport: DESKTOP, reducedMotion: 'reduce' });

for (const { code, dir } of LOCALES) {
    test(`every page in ${code} is in its language, has one main heading and its landmarks, and fits a 320 px screen`, async ({ page, baseURL }) => {
        test.setTimeout(10 * 60_000);

        const origin = new URL(baseURL ?? '').origin;
        const queue = [`/${code}`];
        const seen = new Set(queue);

        for (let path = queue.shift(); path !== undefined; path = queue.shift()) {
            await page.setViewportSize(DESKTOP);
            const response = await page.goto(path);

            expect(response?.status(), `${path} answers`).toBe(200);
            await expect(page.locator('html'), path).toHaveAttribute('lang', code);
            await expect(page.locator('html'), path).toHaveAttribute('dir', dir);
            await expect(page.locator('h1'), `${path} has one main heading`).toHaveCount(1);
            await expect(page.locator('h1'), path).toBeVisible();
            expect((await page.title()).trim(), `${path} has a title`).not.toBe('');
            await expect(page.getByRole('banner'), path).toHaveCount(1);
            await expect(page.getByRole('main'), path).toHaveCount(1);
            await expect(page.getByRole('contentinfo'), path).toHaveCount(1);
            await expect(page.locator('img:not([alt])'), `${path}: every image has a text alternative (empty when decorative)`).toHaveCount(0);

            const unsafeNewTabLinks = await page
                .locator('a[target="_blank"]')
                .evaluateAll((links) => links.filter((link) => !/\bnoopener\b/.test(link.getAttribute('rel') ?? '')).map((link) => link.outerHTML.slice(0, 160)));
            expect(unsafeNewTabLinks, `${path}: links that open a new tab give it no access to this page`).toEqual([]);

            await page.setViewportSize({ width: 320, height: 800 });
            expect(await overflowingElements(page), `${path} scrolls sideways at 320 px`).toEqual([]);

            const links = await page.locator('a[href]').evaluateAll((anchors) => anchors.map((anchor) => (anchor as HTMLAnchorElement).href));

            for (const href of links) {
                const url = new URL(href);
                const key = url.pathname + url.search;
                const inThisLanguage = url.pathname === `/${code}` || url.pathname.startsWith(`/${code}/`);

                if (url.origin === origin && inThisLanguage && !NOT_A_PAGE.test(url.pathname) && !seen.has(key) && seen.size < MAX_PAGES) {
                    seen.add(key);
                    queue.push(key);
                }
            }
        }

        // The demo content has services, projects, articles and pages in every language.
        expect(seen.size, 'pages found').toBeGreaterThan(15);
    });
}
