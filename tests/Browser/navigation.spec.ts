import { DESKTOP, expect, isSameDocument, markDocument, PHONE, test } from './fixtures';

test.describe('on a large screen', () => {
    test.use({ viewport: DESKTOP });

    test('links of the site load pages in place, move focus to the content and announce the new title', async ({ page }) => {
        await page.goto('/en');
        await markDocument(page);
        const homeTitle = await page.title();

        await page.getByRole('navigation', { name: 'Main' }).getByRole('link', { name: 'News', exact: true }).click();

        await expect(page).toHaveURL(/\/en\/news$/);
        await expect(page).not.toHaveTitle(homeTitle);
        await expect(page.locator('h1')).toHaveCount(1);
        await expect(page.locator('#main')).toBeFocused();
        await expect(page.locator('p[aria-live="polite"]')).toHaveText(await page.title());
        await expect(page.getByRole('navigation', { name: 'Main' }).getByRole('link', { name: 'News', exact: true })).toHaveAttribute('aria-current', 'page');
        expect(await isSameDocument(page), 'no full page load').toBe(true);

        await page.goBack();

        await expect(page).toHaveURL(/\/en$/);
        await expect(page).toHaveTitle(homeTitle);
        expect(await isSameDocument(page), 'no full page load').toBe(true);
    });

    test('the skip link is the first keyboard stop and leads to the content', async ({ page }) => {
        await page.goto('/en');
        await expect(page.locator('h1')).toBeVisible();

        await page.keyboard.press('Tab');
        const skipLink = page.getByRole('link', { name: 'Skip to main content' });

        await expect(skipLink).toBeFocused();
        await expect(skipLink).toBeInViewport();

        await page.keyboard.press('Enter');

        await expect(page.locator('#main')).toBeFocused();
    });

    test('the services panel opens from the keyboard and closes with Escape', async ({ page }) => {
        await page.goto('/en');
        const trigger = page.getByRole('navigation', { name: 'Main' }).getByRole('button', { name: 'Services' });
        const panel = page.locator(`[id="${await trigger.getAttribute('aria-controls')}"]`);

        await trigger.focus();
        await page.keyboard.press('Enter');

        await expect(trigger).toHaveAttribute('aria-expanded', 'true');
        await expect(panel).toBeVisible();

        await page.keyboard.press('Tab');
        await expect(panel.getByRole('link').first()).toBeFocused();

        await page.keyboard.press('Escape');

        await expect(panel).toBeHidden();
        await expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await expect(trigger).toBeFocused();
    });

    test('a service chosen in the panel opens in place and the panel closes', async ({ page }) => {
        await page.goto('/en');
        await markDocument(page);
        const trigger = page.getByRole('navigation', { name: 'Main' }).getByRole('button', { name: 'Services' });
        const panel = page.locator(`[id="${await trigger.getAttribute('aria-controls')}"]`);

        await trigger.click();
        const service = panel.getByRole('listitem').getByRole('link').first();
        const href = await service.getAttribute('href');
        await service.click();

        await expect(page).toHaveURL(href ?? '');
        await expect(panel).toBeHidden();
        await expect(page.locator('h1')).toHaveCount(1);
        expect(await isSameDocument(page), 'no full page load').toBe(true);
    });

    test('another language loads the same page in that language and direction', async ({ page }) => {
        await page.goto('/en/news');
        await markDocument(page);

        await page.getByRole('banner').getByRole('button', { name: /Change language/ }).click();
        await page.getByRole('banner').getByRole('link', { name: 'العربية' }).click();

        await expect(page).toHaveURL(/\/ar\/news$/);
        await expect(page.locator('html')).toHaveAttribute('lang', 'ar');
        await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
        await expect(page.locator('h1')).toHaveCount(1);
        expect(await isSameDocument(page), 'a full page load (language, direction and fonts change)').toBe(false);
    });

    test('the projects rail moves one project at a time with its buttons, in reading direction', async ({ page }) => {
        for (const locale of ['en', 'ar']) {
            await page.goto(`/${locale}`);
            const band = page.locator('section[aria-labelledby="home-projects"]');
            const buttons = band.locator('button[aria-controls]');
            const [previous, next] = [buttons.first(), buttons.last()];
            const first = band.getByRole('listitem').first();
            const start = (await first.boundingBox())?.x ?? 0;

            await expect(buttons).toHaveCount(2);
            await expect(previous).toHaveAttribute('aria-disabled', 'true');

            await next.click();

            // The first project moves out towards the start of the reading direction; the button keeps the focus.
            await expect.poll(async () => Math.sign(((await first.boundingBox())?.x ?? start) - start)).toBe(locale === 'ar' ? 1 : -1);
            await expect(previous).toHaveAttribute('aria-disabled', 'false');
            await expect(next).toBeFocused();

            await previous.click();

            await expect.poll(async () => Math.round(((await first.boundingBox())?.x ?? 0) - start)).toBe(0);
            await expect(previous).toHaveAttribute('aria-disabled', 'true');
        }
    });

    test('client quotes are tabs that arrow keys move between, in reading direction', async ({ page }) => {
        for (const [locale, forward] of [
            ['en', 'ArrowRight'],
            ['ar', 'ArrowLeft'],
        ] as const) {
            await page.goto(`/${locale}`);
            await expect(page.locator('h1')).toBeVisible();
            const tabs = page.getByRole('tab');

            test.skip((await tabs.count()) < 2, 'The demo content has fewer than two quotes.');

            await tabs.first().focus();
            await page.keyboard.press(forward);

            await expect(tabs.nth(1)).toBeFocused();
            await expect(tabs.nth(1)).toHaveAttribute('aria-selected', 'true');
            await expect(page.getByRole('tabpanel')).toHaveCount(1);

            await page.keyboard.press('Home');

            await expect(tabs.first()).toHaveAttribute('aria-selected', 'true');
        }
    });
});

test.describe('on a phone', () => {
    test.use({ viewport: PHONE });

    test('the menu opens with focus inside it and closes with Escape or after a visit', async ({ page }) => {
        await page.goto('/en');
        const menu = page.locator('#site-mobile-nav');

        await page.getByRole('button', { name: 'Open menu' }).click();

        await expect(menu).toBeVisible();
        await expect(page.getByRole('button', { name: 'Close menu' })).toHaveAttribute('aria-expanded', 'true');
        await expect(menu.getByRole('link').first()).toBeFocused();

        await page.keyboard.press('Escape');

        await expect(menu).toBeHidden();
        await expect(page.getByRole('button', { name: 'Open menu' })).toBeFocused();

        await page.getByRole('button', { name: 'Open menu' }).click();
        await menu.getByRole('link', { name: 'Projects', exact: true }).click();

        await expect(page).toHaveURL(/\/en\/projects$/);
        await expect(menu).toBeHidden();
        await expect(page.getByRole('button', { name: 'Open menu' })).toHaveAttribute('aria-expanded', 'false');
    });

    test('on the home page, "Start a project" in the menu closes it and shows the contact form', async ({ page }) => {
        await page.goto('/en');
        const menu = page.locator('#site-mobile-nav');

        await page.getByRole('button', { name: 'Open menu' }).click();
        await menu.getByRole('link', { name: 'Start a project' }).click();

        await expect(page).toHaveURL(/\/en#contact$/);
        await expect(menu).toBeHidden();
        await expect(page.locator('#contact').getByRole('heading', { name: 'Send us a message' })).toBeInViewport();
    });
});
