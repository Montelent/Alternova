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
