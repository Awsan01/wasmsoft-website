import type { Page } from '@playwright/test';
import { DESKTOP, expect, isSameDocument, markDocument, overflowingElements, PHONE, test } from './fixtures';

/**
 * The dashboard in a real browser: signing in, editing in several
 * languages, choosing images, reordering, read-only access, the Arabic
 * (right-to-left) dashboard and the navigation drawer of small screens.
 *
 * The demo accounts are those of DevelopmentSeeder (one per role).
 * Tests that save something work on items they create themselves, or put
 * things back as they were, and never publish: the public site, tested at
 * the same time, does not change.
 */
type Role = 'administrator' | 'editor' | 'author' | 'viewer';

const PASSWORD = process.env.DASHBOARD_PASSWORD ?? 'Wasmsoft-Dev-2026';

async function signIn(page: Page, role: Role): Promise<void> {
    await page.goto('/admin/login');
    await page.getByRole('textbox', { name: 'Email address' }).fill(`${role}@wasmsoft.test`);
    await page.getByRole('textbox', { name: 'Password' }).fill(PASSWORD);
    await page.getByRole('button', { name: 'Sign in' }).click();
    await expect(page).toHaveURL(/\/admin$/);
}

/**
 * A field of the visible language tab. The other languages' fields have
 * the same labels but are hidden, and role queries leave hidden elements out.
 */
function field(page: Page, name: string) {
    return page.getByRole('textbox', { name, exact: true });
}

/** A status message shown after an action ("Saved"), announced politely. */
function message(page: Page, text: string | RegExp) {
    return page.getByRole('main').getByRole('status').filter({ hasText: text });
}

test.describe('on a computer', () => {
    test.use({ viewport: DESKTOP });

    test('signing in leads to the overview, and signing out back to the sign-in page', async ({ page }) => {
        await signIn(page, 'administrator');

        await expect(page.locator('h1')).toHaveText('Welcome, Demo Administrator');
        await expect(page).toHaveTitle(/Overview/);

        await page.getByRole('button', { name: 'Account menu' }).click();
        await page.getByRole('button', { name: 'Sign out' }).click();

        await expect(page).toHaveURL(/\/admin\/login$/);
        await page.goto('/admin');
        await expect(page).toHaveURL(/\/admin\/login$/);
    });

    test('pages load in place, move focus to the content and keep the address', async ({ page }) => {
        await signIn(page, 'editor');
        await markDocument(page);

        await page.getByRole('navigation', { name: 'Dashboard navigation' }).getByRole('link', { name: 'Services' }).click();

        await expect(page).toHaveURL(/\/admin\/services$/);
        await expect(page.locator('h1')).toHaveText('Services');
        await expect(page.locator('main')).toBeFocused();
        await expect(page.getByRole('navigation', { name: 'Dashboard navigation' }).getByRole('link', { name: 'Services' })).toHaveAttribute('aria-current', 'page');
        expect(await isSameDocument(page)).toBe(true);
    });

    test('a page is created once its errors are corrected, and deleted after confirmation', async ({ page }) => {
        await signIn(page, 'editor');
        await page.goto('/admin/pages/create');

        await page.getByRole('button', { name: 'Create' }).click();

        const summary = page.getByRole('alert').filter({ hasText: 'Please correct' });
        await expect(summary).toBeFocused();
        await expect(field(page, 'Title')).toHaveAttribute('aria-invalid', 'true');
        await expect(page.getByRole('tab', { name: /English/ })).toContainText('Needs attention');

        // Arabic is typed right to left, whatever the dashboard language.
        await page.getByRole('tab', { name: 'العربية' }).click();
        await expect(page.locator('#field-translations-ar-title')).toHaveAttribute('dir', 'rtl');
        await page.getByRole('tab', { name: /English/ }).click();

        const title = `Browser test page ${Date.now()}`;
        await field(page, 'Title').fill(title);
        await page.getByRole('button', { name: 'Create' }).click();

        await expect(page).toHaveURL(/\/admin\/pages\/\d+\/edit$/);
        await expect(page.locator('h1')).toHaveText(title);
        await expect(message(page, 'Created successfully.')).toBeVisible();
        // The address was made from the title; the languages not written yet are marked.
        await expect(field(page, 'URL slug')).toHaveValue(/^browser-test-page-\d+$/);
        await expect(page.getByRole('tab', { name: /Türkçe/ })).toContainText('Not translated');

        // Cancelling the confirmation keeps the page.
        await page.getByRole('button', { name: 'Delete' }).click();
        const dialog = page.getByRole('dialog');
        await expect(dialog.getByRole('button', { name: 'Cancel' })).toBeFocused();
        await page.keyboard.press('Escape');
        await expect(dialog).toBeHidden();

        await page.getByRole('button', { name: 'Delete' }).click();
        await dialog.getByRole('button', { name: 'Delete' }).click();

        await expect(page).toHaveURL(/\/admin\/pages$/);
        await expect(message(page, 'Deleted.')).toBeVisible();
        await expect(page.getByRole('main')).not.toContainText(title);
    });

    test('after a refused save, everything typed and unticked is still there', async ({ page }) => {
        await signIn(page, 'editor');
        await page.goto('/admin/posts');
        await page.getByRole('main').getByRole('table').getByRole('link').first().click();
        await expect(page).toHaveURL(/\/admin\/posts\/\d+\/edit$/);

        const tags = page.getByRole('group', { name: 'Tags' }).getByRole('checkbox');
        await tags.first().check();
        await tags.first().uncheck();
        await field(page, 'Summary').fill('A summary typed before the error.');
        // A language that has content needs its title.
        await field(page, 'Title').fill('');
        await page.getByRole('button', { name: 'Save changes' }).click();

        await expect(page.getByRole('alert').filter({ hasText: 'Please correct' })).toBeFocused();
        await expect(field(page, 'Title')).toHaveAttribute('aria-invalid', 'true');
        await expect(field(page, 'Summary')).toHaveValue('A summary typed before the error.');
        await expect(tags.first()).not.toBeChecked();
    });

    test('images are chosen from the library in a dialog, from the keyboard', async ({ page }) => {
        await signIn(page, 'editor');
        await page.goto('/admin/projects');
        await page.getByRole('main').getByRole('table').getByRole('link').first().click();
        await expect(page).toHaveURL(/\/admin\/projects\/\d+\/edit$/);

        const cover = page.getByRole('group', { name: 'Cover image' });
        const choose = cover.getByRole('button', { name: 'Choose image' });
        await choose.click();

        const library = page.getByRole('dialog', { name: 'Choose an image' });
        const firstImage = library.getByRole('button', { name: /^Select / }).first();
        await expect(firstImage).toBeFocused();

        // Escape closes it, and focus goes back to the button that opened it.
        await page.keyboard.press('Escape');
        await expect(library).toBeHidden();
        await expect(choose).toBeFocused();

        await page.keyboard.press('Enter');
        await expect(firstImage).toBeFocused();
        const name = ((await firstImage.textContent()) ?? '').replace(/^Select /, '');
        await page.keyboard.press('Enter');

        await expect(library).toBeHidden();
        await expect(cover).toContainText(name);

        // The gallery takes several images, each removable.
        const gallery = page.getByRole('group', { name: 'Gallery' });
        const before = await gallery.getByRole('listitem').count();
        await gallery.getByRole('button', { name: 'Add images' }).click();
        await library.getByRole('button', { name: /^Select / }).last().click();
        await expect(gallery.getByRole('listitem')).toHaveCount(before + 1);
        await gallery.getByRole('listitem').last().getByRole('button', { name: /^Remove / }).click();
        await expect(gallery.getByRole('listitem')).toHaveCount(before);

        // Searching inside the dialog does not send the project form.
        await gallery.getByRole('button', { name: 'Add images' }).click();
        await library.getByRole('searchbox').fill('no image is called this');
        await library.getByRole('button', { name: 'Search' }).click();
        await expect(library).toContainText('No images found.');
        await expect(page).toHaveURL(/\/edit$/);
        await expect(message(page, /saved/i)).toHaveCount(0);
    });

    test('services are reordered with the arrow buttons, and focus follows the moved row', async ({ page }) => {
        await signIn(page, 'editor');
        await page.goto('/admin/services');

        const rows = page.getByRole('main').getByRole('table').getByRole('row');
        const first = ((await rows.nth(1).getByRole('link').first().textContent()) ?? '').trim();

        await rows.nth(1).getByRole('button', { name: `Move down: ${first}` }).click();

        await expect(message(page, 'The order has been updated.')).toBeVisible();
        await expect(rows.nth(2).getByRole('link').first()).toHaveText(first);
        await expect(page.getByRole('button', { name: `Move down: ${first}` })).toBeFocused();

        // Back to the top: "move up" no longer applies there, so focus moves to "move down".
        await page.getByRole('button', { name: `Move up: ${first}` }).click();

        await expect(rows.nth(1).getByRole('link').first()).toHaveText(first);
        await expect(page.getByRole('button', { name: `Move up: ${first}` })).toBeDisabled();
        await expect(page.getByRole('button', { name: `Move down: ${first}` })).toBeFocused();
    });

    test('viewers read every language of an item but cannot change it', async ({ page }) => {
        await signIn(page, 'viewer');
        await page.goto('/admin/services');

        await expect(page.getByRole('link', { name: 'New service' })).toHaveCount(0);
        await expect(page.getByRole('button', { name: /^Move/ })).toHaveCount(0);

        await page.getByRole('main').getByRole('table').getByRole('link').first().click();

        await expect(page.getByRole('main').getByRole('status').first()).toContainText('read-only');
        await expect(field(page, 'Title')).toHaveAttribute('readonly', '');
        await expect(page.getByRole('combobox', { name: 'Status' })).toBeDisabled();
        await expect(page.getByRole('button', { name: 'Save changes' })).toBeDisabled();
        await expect(page.getByRole('button', { name: 'Delete' })).toHaveCount(0);

        await page.getByRole('tab', { name: 'العربية' }).click();
        await expect(page.locator('#field-translations-ar-title')).toBeVisible();
    });

    test('the dashboard switches to Arabic, right to left, and back without reloading', async ({ page }) => {
        // The only test signed in as the author: the dashboard language is saved on the account.
        await signIn(page, 'author');
        await markDocument(page);

        await page.getByRole('button', { name: /Dashboard language/ }).click();
        await page.locator('[lang="ar"]', { hasText: 'العربية' }).click();

        await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
        await expect(page.locator('html')).toHaveAttribute('lang', 'ar');
        const sidebar = await page.getByRole('complementary').boundingBox();
        const main = await page.locator('main').boundingBox();
        expect(sidebar && main && sidebar.x > main.x, 'the sidebar is on the right').toBe(true);
        expect(await isSameDocument(page)).toBe(true);

        await page.getByRole('button', { name: /العربية/ }).click();
        await page.locator('[lang="en"]', { hasText: 'English' }).click();

        await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
        await expect(page.locator('h1')).toHaveText('Welcome, Demo Author');
    });
});

test.describe('on a phone', () => {
    test.use({ viewport: PHONE });

    test('the navigation drawer opens with focus inside it and closes with Escape', async ({ page }) => {
        await signIn(page, 'administrator');

        expect(await overflowingElements(page)).toEqual([]);

        const open = page.getByRole('button', { name: 'Open navigation' });
        await open.click();

        const drawer = page.getByRole('dialog', { name: 'Dashboard navigation' });
        await expect(drawer).toBeVisible();
        await expect(drawer.locator(':focus')).toHaveCount(1);

        await page.keyboard.press('Escape');
        await expect(drawer).toBeHidden();
        await expect(open).toBeFocused();

        // A link chosen in the drawer opens its page and closes the drawer.
        await open.click();
        await drawer.getByRole('link', { name: 'Media library' }).click();
        await expect(page).toHaveURL(/\/admin\/media$/);
        await expect(drawer).toBeHidden();
        expect(await overflowingElements(page)).toEqual([]);
    });
});
