import { DESKTOP, expect, isSameDocument, LOCALES, markDocument, test } from './fixtures';

test.use({ viewport: DESKTOP });

test('project categories and the search narrow the list in place', async ({ page }) => {
    await page.goto('/en/projects');
    await markDocument(page);
    const filters = page.getByRole('navigation', { name: 'Filter projects by category' });
    const category = filters.getByRole('link').nth(1);

    await expect(filters.getByRole('link').first()).toHaveAttribute('aria-current', 'page');

    await category.click();

    await expect(page).toHaveURL(/\/en\/projects\?category=/);
    await expect(category).toHaveAttribute('aria-current', 'page');
    await expect(filters.getByRole('link').first()).not.toHaveAttribute('aria-current');

    await page.getByRole('searchbox', { name: 'Search projects' }).fill('no-such-project-anywhere');
    await page.getByRole('search').getByRole('button', { name: 'Search' }).click();

    await expect(page).toHaveURL(/q=no-such-project-anywhere/);
    await expect(page.getByText('No projects match your search.')).toBeVisible();
    // The search kept the chosen category.
    await expect(page).toHaveURL(/category=/);

    await page.getByRole('main').getByRole('link', { name: 'Show all projects' }).click();

    await expect(page).toHaveURL(/\/en\/projects$/);
    await expect(page.getByRole('main').getByRole('listitem').first()).toBeVisible();
    expect(await isSameDocument(page), 'no full page load').toBe(true);
});

for (const { code, dir } of LOCALES) {
    test(`a missing page in ${code} answers 404 within the site and leads back home`, async ({ page }) => {
        const response = await page.goto(`/${code}/no-such-page`);

        expect(response?.status()).toBe(404);
        await expect(page.locator('html')).toHaveAttribute('dir', dir);
        await expect(page.locator('h1')).toHaveCount(1);
        await expect(page.getByRole('banner')).toBeVisible();
        await expect(page.getByRole('contentinfo')).toBeVisible();

        await markDocument(page);
        await page.getByRole('main').locator(`a[href$="/${code}"]`).first().click();

        await expect(page).toHaveURL(new RegExp(`/${code}$`));
        await expect(page.locator('h1')).toHaveCount(1);
        expect(await isSameDocument(page), 'no full page load').toBe(true);
    });
}
