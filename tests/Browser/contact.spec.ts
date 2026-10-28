import { DESKTOP, expect, expectErrorResponse, test } from './fixtures';

test.use({ viewport: DESKTOP });

test('the home page ends with the contact form, which is sent without leaving the page', async ({ page }) => {
    await page.goto('/en');
    const section = page.locator('#contact');
    const name = section.getByLabel('Your name');

    // "Start a project" leads to the form further down the same page.
    await page.getByRole('main').getByRole('link', { name: 'Start a project' }).first().click();

    await expect(page).toHaveURL(/\/en#contact$/);
    await expect(section.getByRole('heading', { name: 'Send us a message' })).toBeInViewport();

    // Moving into the form asks for its token; sent with mistakes, it lists them in place.
    const opened = Date.now();
    await name.fill('Browser test');
    expectErrorResponse(page, 422, /^\/en\/contact$/);
    await section.getByRole('button', { name: 'Send message' }).click();

    const summary = section.getByRole('alert');

    await expect(summary).toBeFocused();
    await expect(summary).toContainText('Please correct');
    await expect(section.getByLabel('Email address')).toHaveAttribute('aria-invalid', 'true');
    await expect(name).toHaveValue('Browser test');

    await summary.getByRole('link').first().click();
    await expect(section.getByLabel('Email address')).toBeFocused();

    await section.getByLabel('Email address').fill('browser-test@example.com');
    await section.getByRole('textbox', { name: 'Message', exact: true }).fill('Hello, this message was sent from the home page by the browser tests.');
    await section.getByRole('checkbox').check();
    // People take longer than the few seconds within which the spam check suspects a bot.
    await page.waitForTimeout(Math.max(0, 3500 - (Date.now() - opened)));
    await section.getByRole('button', { name: 'Send message' }).click();

    const confirmation = section.getByRole('status').filter({ hasText: 'Thank you' });

    await expect(confirmation).toBeFocused();
    // Still the home page: the confirmation replaced the form in place.
    expect(new URL(page.url()).pathname).toBe('/en');

    await section.getByRole('button', { name: 'Send another message' }).click();

    await expect(name).toBeFocused();
    await expect(name).toHaveValue('');
});

test('a home page form that was open for too long is sent on the next try', async ({ page }) => {
    // The first token the form receives is no longer valid (here: it was never issued by the site).
    let answered = false;
    await page.route('**/en/contact/token', async (route) => {
        if (answered) {
            await route.continue();
        } else {
            answered = true;
            await route.fulfill({ json: { token: 'expired' } });
        }
    });

    await page.goto('/en');
    const section = page.locator('#contact');

    await section.getByLabel('Your name').fill('Browser test');
    await section.getByLabel('Email address').fill('browser-test@example.com');
    await section.getByRole('textbox', { name: 'Message', exact: true }).fill('Hello, this message was sent after the form had expired.');
    await section.getByRole('checkbox').check();
    expectErrorResponse(page, 422, /^\/en\/contact$/);
    await section.getByRole('button', { name: 'Send message' }).click();

    // Refused once, with what was typed kept; a new token is fetched meanwhile.
    await expect(section.getByRole('alert')).toContainText('This form has expired');
    await expect(section.getByLabel('Your name')).toHaveValue('Browser test');

    // People take a moment to read the message before sending again.
    await page.waitForTimeout(3500);
    await section.getByRole('button', { name: 'Send message' }).click();

    await expect(section.getByRole('status').filter({ hasText: 'Thank you' })).toBeFocused();
});

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
