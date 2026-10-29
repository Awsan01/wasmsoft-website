import { DESKTOP, expect, isSameDocument, markDocument, overflowingElements, PHONE, test } from './fixtures';

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

    test('a panel opened with the mouse stays open while the pointer moves through it, also while its links load their pages in advance', async ({ page }) => {
        await page.goto('/en');
        const trigger = page.getByRole('navigation', { name: 'Main' }).getByRole('button', { name: 'Services' });
        const panel = page.locator(`[id="${await trigger.getAttribute('aria-controls')}"]`);
        const services = panel.getByRole('listitem').getByRole('link');

        await trigger.hover();
        await expect(panel).toBeVisible();

        // Down into the panel, then from one service to another. Resting on a link loads its page in the
        // background (a prefetch), which used to close the panel.
        for (const index of [0, 4, 2]) {
            const link = services.nth(index);
            const box = await link.boundingBox();
            const href = new URL((await link.getAttribute('href')) ?? '', page.url()).href;
            const prefetch = page.waitForRequest((request) => request.url() === href && request.headers()['x-inertia'] === 'true');

            await page.mouse.move((box?.x ?? 0) + 24, (box?.y ?? 0) + 12, { steps: 12 });
            await prefetch;
            await page.waitForTimeout(400);

            await expect(panel).toBeVisible();
            await expect(trigger).toHaveAttribute('aria-expanded', 'true');
        }

        // On its way from the menu item into the panel, the pointer may pass over the next item: that does not
        // switch panels. The page's time stands still from here on (it moves only with runFor), so the movement
        // takes no time however busy the machine is.
        const projects = page.getByRole('navigation', { name: 'Main' }).getByRole('button', { name: 'Projects' });
        const [from, over, row] = await Promise.all([trigger.boundingBox(), projects.boundingBox(), services.first().boundingBox()]);
        const centre = (box: typeof from) => ({ x: (box?.x ?? 0) + (box?.width ?? 0) / 2, y: (box?.y ?? 0) + (box?.height ?? 0) / 2 });

        await page.clock.install();
        await page.clock.pauseAt(new Date(Date.now() + 2000));
        await page.mouse.move(centre(from).x, centre(from).y);
        await page.mouse.move(centre(over).x, centre(over).y, { steps: 3 });
        await page.mouse.move(centre(row).x, centre(row).y, { steps: 5 });
        await page.clock.runFor(1000);

        await expect(panel).toBeVisible();
        await expect(projects).toHaveAttribute('aria-expanded', 'false');

        // Resting on another item switches panels.
        await page.mouse.move(centre(over).x, centre(over).y, { steps: 4 });
        await page.clock.runFor(1000);
        await expect(panel).toBeHidden();
        await expect(projects).toHaveAttribute('aria-expanded', 'true');

        // Away from the menu and the panel, it closes.
        await page.mouse.move(640, 880, { steps: 6 });
        await page.clock.runFor(1000);
        await expect(projects).toHaveAttribute('aria-expanded', 'false');
        await expect(page.locator(`[id="${await projects.getAttribute('aria-controls')}"]`)).toBeHidden();
    });

    test('clicking a menu item right after its panel opened under the pointer keeps the panel open', async ({ page }) => {
        await page.goto('/en');
        const trigger = page.getByRole('navigation', { name: 'Main' }).getByRole('button', { name: 'Services' });
        const panel = page.locator(`[id="${await trigger.getAttribute('aria-controls')}"]`);
        const box = await trigger.boundingBox();
        const center = { x: (box?.x ?? 0) + (box?.width ?? 0) / 2, y: (box?.y ?? 0) + (box?.height ?? 0) / 2 };

        // The page's time moves only with runFor.
        await page.clock.install();
        await page.clock.pauseAt(new Date(Date.now() + 2000));

        await page.mouse.move(center.x, center.y);
        await page.clock.runFor(300);
        await expect(panel).toBeVisible();

        // The click that was meant to open it.
        await page.mouse.click(center.x, center.y);
        await page.clock.runFor(300);
        await expect(panel).toBeVisible();

        // A later click closes it.
        await page.clock.runFor(2000);
        await page.mouse.click(center.x, center.y);
        await expect(panel).toBeHidden();
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

    test('the projects carousel shows three whole cards and moves one card at a time with its buttons, in reading direction', async ({ page }) => {
        // With less motion asked for, the cards change places without sliding, so they are where they go at once.
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.clock.install();

        for (const [locale, pause, play, showing] of [
            ['en', 'Stop moving automatically', 'Move automatically', 'Showing items 2 to 4 of 6'],
            ['ar', 'إيقاف التحريك التلقائي', 'تشغيل التحريك التلقائي', 'تُعرض العناصر من 2 إلى 4 من أصل 6'],
        ] as const) {
            // The page's time stands still, so that the cards do not move on their own meanwhile.
            await page.clock.pauseAt(new Date(Date.now() + 1000));
            await page.goto(`/${locale}`);
            const band = page.locator('section[aria-labelledby="home-projects"]');
            const viewport = band.locator('.carousel-viewport');
            const cards = viewport.locator('.carousel-track > *');
            const buttons = band.locator('button[aria-controls]');
            const [rotation, previous, next] = [buttons.nth(0), buttons.nth(1), buttons.nth(2)];

            /** Which cards lie wholly inside the band's viewport; every other card must lie wholly outside it. */
            const cardsInView = async (): Promise<number[] | string> => {
                const frame = await viewport.boundingBox();
                const boxes = await cards.evaluateAll((elements) => elements.map((element) => element.getBoundingClientRect().toJSON() as DOMRect));
                const inside = boxes.flatMap((box, index) => (box.left >= (frame?.x ?? 0) - 1 && box.right <= (frame?.x ?? 0) + (frame?.width ?? 0) + 1 ? [index] : []));
                const cut = boxes.findIndex((box, index) => !inside.includes(index) && box.right > (frame?.x ?? 0) + 1 && box.left < (frame?.x ?? 0) + (frame?.width ?? 0) - 1);

                return cut >= 0 ? `card ${cut} is cut by the edge` : inside;
            };

            await expect(cards).toHaveCount(6);
            await expect(buttons).toHaveCount(3);
            // They move on their own also for visitors who asked for less motion, until they are stopped.
            await expect(rotation).toHaveAttribute('aria-label', pause);
            await rotation.click();
            await expect(rotation).toHaveAttribute('aria-label', play);
            await expect.poll(cardsInView).toEqual([0, 1, 2]);
            await expect(viewport.locator('.carousel-track > :not([inert])')).toHaveCount(3);

            // The second card follows the first in the reading direction.
            const [first, second] = [await cards.nth(0).boundingBox(), await cards.nth(1).boundingBox()];
            expect(Math.sign((second?.x ?? 0) - (first?.x ?? 0))).toBe(locale === 'ar' ? -1 : 1);

            await next.click();

            await expect.poll(cardsInView).toEqual([1, 2, 3]);
            await expect(cards.nth(0)).toHaveAttribute('inert', '');
            await expect(cards.nth(3)).not.toHaveAttribute('inert');
            await expect(next).toBeFocused();
            await expect(band.locator('[aria-live="polite"]')).toHaveText(showing);

            // From the first cards, "previous" goes on to the last ones.
            await previous.click();
            await expect.poll(cardsInView).toEqual([0, 1, 2]);
            await previous.click();
            await expect.poll(cardsInView).toEqual([3, 4, 5]);

            // Time moves again for the page to be drawn and measured.
            await page.clock.resume();
            expect(await overflowingElements(page)).toEqual([]);
        }
    });

    test('the projects carousel moves on its own, holds still while the pointer moves over it and stops with its pause button', async ({ page }) => {
        // The page's time moves only with runFor, from before the page loads.
        await page.clock.install();
        await page.clock.pauseAt(new Date(Date.now() + 1000));
        await page.goto('/en');

        const band = page.locator('section[aria-labelledby="home-projects"]');
        const firstInView = band.locator('.carousel-track > :not([inert])').first();
        const pause = band.getByRole('button', { name: 'Stop moving automatically' });

        // The band is below the first screen; the pointer can only rest on what is in the window.
        await band.evaluate((element) => element.scrollIntoView({ block: 'center', behavior: 'instant' }));
        const viewport = await band.locator('.carousel-viewport').boundingBox();

        const filled = () => band.locator('.carousel-progress-fill').evaluate((bar) => Number(new DOMMatrix(getComputedStyle(bar).transform).a.toFixed(2)));

        await expect(firstInView).toHaveAttribute('aria-label', '1 of 6');

        // Halfway through the wait, the bar under the cards is half full; then they move on.
        await page.clock.runFor(2250);
        expect(await filled()).toBeCloseTo(0.5, 1);
        await page.clock.runFor(2400);
        await expect(firstInView).toHaveAttribute('aria-label', '2 of 6');

        // A pointer moving over the cards holds them still…
        for (let step = 0; step < 6; step += 1) {
            await page.mouse.move((viewport?.x ?? 0) + 40 + step * 20, (viewport?.y ?? 0) + 40);
            await page.clock.runFor(2000);
        }
        await expect(firstInView).toHaveAttribute('aria-label', '2 of 6');

        // …but once it rests there, they move on after a moment.
        await page.clock.runFor(3000 + 4700);
        await expect(firstInView).toHaveAttribute('aria-label', '3 of 6');

        // When the page scrolls under a pointer that stays still, they move on too.
        await page.mouse.move((viewport?.x ?? 0) + 40, (viewport?.y ?? 0) + 40);
        await page.mouse.wheel(0, 1600);
        await page.mouse.move((viewport?.x ?? 0) + 41, (viewport?.y ?? 0) + 40);
        await page.clock.runFor(4700);
        await expect(firstInView).toHaveAttribute('aria-label', '4 of 6');
        await band.evaluate((element) => element.scrollIntoView({ block: 'center', behavior: 'instant' }));

        // The pause button stops them until the visitor starts them again.
        const button = await pause.boundingBox();
        await page.mouse.click((button?.x ?? 0) + 10, (button?.y ?? 0) + 10);
        await page.mouse.move(5, 5);
        await page.clock.runFor(20000);
        await expect(firstInView).toHaveAttribute('aria-label', '4 of 6');
        await expect(band.getByRole('button', { name: 'Move automatically' })).toBeVisible();
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

    test('the projects carousel shows one whole card, and a swipe moves to the next one', async ({ page }) => {
        await page.emulateMedia({ reducedMotion: 'reduce' });
        // The page's time stands still: only the swipe moves the cards.
        await page.clock.install();
        await page.clock.pauseAt(new Date(Date.now() + 1000));
        await page.goto('/en');
        const viewport = page.locator('section[aria-labelledby="home-projects"] .carousel-viewport');
        const inView = viewport.locator('.carousel-track > :not([inert])');
        const frame = await viewport.boundingBox();
        const y = (frame?.y ?? 0) + 120;
        const x = (frame?.x ?? 0) + (frame?.width ?? 0) - 40;

        await expect(inView).toHaveCount(1);
        await expect(inView).toHaveAttribute('aria-label', '1 of 6');
        expect(Math.round((await inView.boundingBox())?.width ?? 0)).toBe(Math.round(frame?.width ?? 0));

        // A finger moving towards the start of the line shows the next card.
        await viewport.dispatchEvent('pointerdown', { pointerType: 'touch', pointerId: 7, isPrimary: true, clientX: x, clientY: y });
        await viewport.dispatchEvent('pointerup', { pointerType: 'touch', pointerId: 7, isPrimary: true, clientX: x - 160, clientY: y + 12 });

        await expect(inView).toHaveAttribute('aria-label', '2 of 6');
        await page.clock.resume();
        expect(await overflowingElements(page)).toEqual([]);
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
