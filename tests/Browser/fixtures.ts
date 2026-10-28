import { test as base, expect, type Page } from '@playwright/test';

export { expect };

export const LOCALES = [
    { code: 'ar', dir: 'rtl' },
    { code: 'en', dir: 'ltr' },
    { code: 'tr', dir: 'ltr' },
] as const;

export const DESKTOP = { width: 1280, height: 900 };
export const PHONE = { width: 390, height: 844 };

/** Error answers a test causes on purpose, per page (see expectErrorResponse). */
const expectedErrors = new WeakMap<Page, Array<{ status: number; path: RegExp }>>();

/**
 * Tells the error check below that the next answer with this status from an
 * address matching `path` is intended, such as a form sent with mistakes
 * (422).
 */
export function expectErrorResponse(page: Page, status: number, path: RegExp): void {
    expectedErrors.set(page, [...(expectedErrors.get(page) ?? []), { status, path }]);
}

/**
 * Every test also fails when a page logs an error (a script error, a
 * Content Security Policy violation, a React warning) or a file it needs
 * (script, style, font, image, page data) cannot be loaded, unless the
 * test said it expects that answer (expectErrorResponse).
 */
export const test = base.extend<{ problems: string[] }>({
    problems: [
        async ({ page }, use) => {
            const problems: string[] = [];
            const isExpected = (status: number, url: string): boolean => {
                const expected = expectedErrors.get(page) ?? [];
                const index = expected.findIndex((entry) => entry.status === status && entry.path.test(new URL(url).pathname));

                if (index >= 0) {
                    expected.splice(index, 1);
                }

                return index >= 0;
            };

            page.on('console', (message) => {
                // Error statuses are listed below, with the address that answered them.
                if (message.type() === 'error' && !message.text().startsWith('Failed to load resource')) {
                    problems.push(`console: ${message.text()}`);
                }
            });
            page.on('pageerror', (error) => problems.push(`uncaught: ${error.message}`));
            page.on('response', (response) => {
                // Pages themselves are checked by the tests (a missing page answers 404 on purpose).
                if (response.status() >= 400 && response.request().resourceType() !== 'document' && !isExpected(response.status(), response.url())) {
                    problems.push(`${response.status()}: ${response.url()}`);
                }
            });
            page.on('requestfailed', (request) => {
                const reason = request.failure()?.errorText ?? '';

                // Visits replaced by a newer one, and prefetches no longer needed, are cancelled on purpose.
                if (!reason.includes('ERR_ABORTED')) {
                    problems.push(`failed: ${request.url()} (${reason})`);
                }
            });

            await use(problems);

            expect(problems, 'errors in the browser').toEqual([]);
        },
        { auto: true },
    ],
});

type MarkedWindow = Window & { __sameDocument?: boolean };

/** Marks the loaded document: the mark survives visits Inertia makes in place, not full page loads. */
export async function markDocument(page: Page): Promise<void> {
    await page.evaluate(() => {
        (window as MarkedWindow).__sameDocument = true;
    });
}

export async function isSameDocument(page: Page): Promise<boolean> {
    return page.evaluate(() => (window as MarkedWindow).__sameDocument === true);
}

/** Elements that make the page scroll sideways (WCAG 1.4.10 reflow), as short HTML excerpts. */
export async function overflowingElements(page: Page): Promise<string[]> {
    // Right after the window is resized, lengths in viewport units (vw) can still have their
    // old values; the page is measured as it is drawn at the new size, two frames later.
    await page.evaluate(() => new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve))));

    return page.evaluate(() => {
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
            .map((element) => element.outerHTML.slice(0, 160));
    });
}
