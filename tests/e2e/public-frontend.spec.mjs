import { test, expect } from '@playwright/test';

const baseUrl = process.env.ALTERNOVA_E2E_URL ?? 'http://127.0.0.1:8000';

test('homepage renders and the selected theme survives a reload', async ({ page }) => {
    const response = await page.goto(baseUrl);
    expect(response?.status()).toBe(200);

    const themeToggle = page.getByRole('button', { name: 'Toggle dark mode' });
    await expect(themeToggle).toBeVisible();

    await page.waitForFunction(() => Boolean(window.Alpine && document.documentElement._x_dataStack));

    await themeToggle.click();
    await expect(page.locator('html')).toHaveClass(/dark/);
    await expect.poll(() => page.evaluate(() => localStorage.getItem('alternova-theme'))).toBe('dark');

    await page.reload();
    await expect(page.locator('html')).toHaveClass(/dark/);

    await page.getByRole('button', { name: 'Toggle dark mode' }).click();
    await expect(page.locator('html')).not.toHaveClass(/dark/);
    await expect.poll(() => page.evaluate(() => localStorage.getItem('alternova-theme'))).toBe('light');
});

test('core public pages render in a real browser', async ({ page }) => {
    const paths = [
        '/',
        '/alternatives',
        '/browse/categories',
        '/browse/languages',
        '/browse/licenses',
        '/trending',
        '/leaderboard',
        '/whats-new',
        '/collections',
        '/domains',
        '/contact',
        '/suggest',
        '/suggest-collection',
        '/about',
        '/privacy',
        '/terms',
    ];

    for (const path of paths) {
        const response = await page.goto(new URL(path, baseUrl).toString());
        expect(response?.status(), `Expected ${path} to render successfully`).toBe(200);
        await expect(page.locator('body')).toBeVisible();
    }
});

test('mobile navigation opens and closes with Escape', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(baseUrl);

    const menuButton = page.getByRole('button', { name: 'Open menu' });
    await expect(menuButton).toBeVisible();
    await menuButton.click();
    await expect(page.locator('#mobile-nav')).toBeVisible();
    await expect(page.locator('#mobile-nav').getByRole('link', { name: 'Contact', exact: true })).toBeVisible();

    await page.keyboard.press('Escape');
    await expect(page.locator('#mobile-nav')).toBeHidden();
    await expect(page.locator('body')).not.toHaveClass(/menu-locked/);
});

test('contact form submits through Livewire and displays the created ticket state', async ({ page }) => {
    await page.goto(new URL('/contact', baseUrl).toString());

    const form = page.locator('form').filter({ has: page.locator('textarea[wire\\:model="message"]') });
    await form.locator('input[wire\\:model="name"]').fill('Browser Test');
    await form.locator('input[wire\\:model="email"]').fill('browser-contact@example.test');
    await form.locator('input[wire\\:model="subject"]').fill('Browser smoke test');
    await form.locator('textarea[wire\\:model="message"]').fill('Testing the public contact form in Chromium.');

    await form.getByRole('button', { name: 'Open support ticket' }).click();
    await expect(page.getByText('Ticket created')).toBeVisible();
    await expect(page.getByRole('link', { name: 'View ticket & replies' })).toBeVisible();
});

test('alternative suggestion form submits and exposes its tracking link', async ({ page }) => {
    await page.goto(new URL('/suggest', baseUrl).toString());

    await page.locator('input[wire\\:model="proprietary_name"]').fill('Example Proprietary Product');
    await page.locator('input[wire\\:model="alternative_name"]').fill('Example Open Alternative');
    await page.locator('input[wire\\:model="repo_url"]').fill('https://github.com/example/example-open-alternative');

    await page.getByRole('button', { name: 'Submit for review' }).click();
    await expect(page.getByText('Thanks — we received your suggestion.')).toBeVisible();
    await expect(page.getByText('Track this submission')).toBeVisible();
});

test('newsletter subscription confirms success and clears the email field', async ({ page }) => {
    await page.goto(baseUrl);

    const email = page.getByPlaceholder('you@example.com');
    await expect(email).toBeVisible();
    await email.fill('browser-newsletter@example.test');
    await page.getByRole('button', { name: 'Subscribe' }).click();

    await expect(page.getByText(/Subscribed — occasional product updates only/)).toBeVisible();
    await expect(email).toHaveValue('');
});
