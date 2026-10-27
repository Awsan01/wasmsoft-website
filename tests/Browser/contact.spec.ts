import { DESKTOP, expect, test } from './fixtures';

test.use({ viewport: DESKTOP });

test('an incomplete message lists its problems, keeps what was typed and leads to each field', async ({ page }) => {
    await page.goto('/en/contact');
    await page.getByLabel('Your name').fill('Layla Haddad');

    await page.getByRole('button', { name: 'Send message' }).click();

    const summary = page.getByRole('alert');
    const email = page.getByLabel('Email address');

    await expect(summary).toBeFocused();
    await expect(summary).toContainText('Please correct');
    await expect(email).toHaveAttribute('aria-invalid', 'true');
    await expect(email).toHaveAttribute('aria-describedby', /field-email-error/);
    await expect(page.getByLabel('Your name')).toHaveValue('Layla Haddad');
    await expect(page.getByLabel('Your name')).not.toHaveAttribute('aria-invalid');
    await expect(page.getByRole('checkbox')).toHaveAttribute('aria-invalid', 'true');

    // The first problem listed is the first field to correct.
    await summary.getByRole('link').first().click();

    await expect(email).toBeFocused();
});

test('a complete message is sent and confirmed', async ({ page }) => {
    const opened = Date.now();
    await page.goto('/en/contact');

    await page.getByLabel('Your name').fill('Browser test');
    await page.getByLabel('Email address').fill('browser-test@example.com');
    await page.getByRole('textbox', { name: 'Message', exact: true }).fill('Hello, this message was sent by the browser tests.');
    await page.getByRole('checkbox').check();
    // People take longer than the few seconds within which the spam check suspects a bot.
    await page.waitForTimeout(Math.max(0, 3500 - (Date.now() - opened)));
    await page.getByRole('button', { name: 'Send message' }).click();

    const confirmation = page.getByRole('status').filter({ hasText: 'Thank you' });

    await expect(confirmation).toBeFocused();
    await expect(page).toHaveURL(/\/en\/contact$/);

    await page.getByRole('link', { name: 'Send another message' }).click();

    await expect(page.getByRole('button', { name: 'Send message' })).toBeVisible();
    await expect(page.getByLabel('Your name')).toHaveValue('');
});

test('a service page leads to the form with that service chosen', async ({ page }) => {
    await page.goto('/en/services');
    const listTitle = (await page.locator('h1').textContent())?.trim() ?? '';
    await page.getByRole('main').locator('a[href*="/en/services/"]').first().click();
    await expect(page).toHaveURL(/\/en\/services\/.+/);
    await expect(page.locator('h1')).not.toHaveText(listTitle);
    const title = (await page.locator('h1').textContent())?.trim() ?? '';

    await page.getByRole('main').getByRole('link', { name: 'Discuss your project' }).first().click();

    await expect(page).toHaveURL(/\/en\/contact\?service=\d+$/);
    await expect(page.getByLabel('What can we help you with?').locator('option:checked')).toHaveText(title);
});
