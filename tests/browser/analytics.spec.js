import AxeBuilder from '@axe-core/playwright';
import {test, expect, signedIn} from './fixtures.js';

// The analytics page with canned data: its charts, their keyboard legends
// and data tables, the refresh button, and axe once everything is drawn.
// The page's data requests are answered here; PostHog is never asked.
test.use({storageState: signedIn});

const weeks = ['2026-10-26', '2026-11-02', '2026-11-09', '2026-11-16', '2026-11-23'];
const series = (f) => Object.fromEntries(weeks.map((week, i) => [week, f(i)]));
const answers = {
    total: {events: {
        popover_open: series((i) => 10 + i * 3), alternative: series((i) => 4 + i),
        ignore: series((i) => 2 + (i % 2)), learning_bites: series((i) => i), check_highlights: series((i) => 20 + i),
    }},
    topSubcategories: {events: {popover_open: {
        gender: {name: 'Gender', url: 'https://example.org/gender', counts: series((i) => 5 + i)},
        age: {name: 'Age', url: 'https://example.org/age', counts: series((i) => 3 + (i % 3))},
    }}},
    topWords: {events: {popover_open: {chairman: 12, manpower: 7}}},
};

test.beforeEach(async ({page}) => {
    await page.route(/\/api\/(user|team)\/analytics/, (route) => {
        const chart = new URL(route.request().url()).searchParams.get('chart');
        route.fulfill({json: {...(answers[chart] || {events: {}}), last_refresh: new Date().toJSON()}});
    });
    await page.goto('/en/user/analytics');
    await page.waitForFunction(() => window.Chart && Chart.getChart('eventsChart'));
});

test('the analytics page has no detectable violations', async ({page}) => {
    const results = await new AxeBuilder({page}).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze();
    expect(results.violations.map((v) => `${v.id}: ${v.help}`)).toEqual([]);
});

test('the legend switches lines with the keyboard', async ({page}) => {
    const second = page.locator('#eventsChart-legend input').nth(1);
    await second.focus();
    await page.keyboard.press('Space');
    expect(await page.evaluate(() => Chart.getChart('eventsChart').isDatasetVisible(1))).toBe(false);
    await page.keyboard.press('Space');
    expect(await page.evaluate(() => Chart.getChart('eventsChart').isDatasetVisible(1))).toBe(true);
});

test('each chart has its data as a table', async ({page}) => {
    const table = page.locator('#eventsChart-data table');
    await expect(table.locator('tbody tr')).toHaveCount(weeks.length);
    await expect(table.locator('thead th').nth(1)).toHaveText(/\S/);
});

test('the refresh button shows only when the data is old, and keeps focus', async ({page}) => {
    const button = page.locator('#refresh-data');
    await expect(button).toBeHidden();
    await page.evaluate(() => {
        document.getElementById('lastRefresh').setAttribute('last_refresh', new Date(Date.now() - 10 * 60000).toJSON());
        updateSection();
    });
    await expect(button).toBeVisible();
    await button.focus();
    await page.evaluate(() => updateSection());
    await expect(button).toBeFocused();
});

test('the analytics page fits 320px', async ({page}) => {
    await page.setViewportSize({width: 320, height: 800});
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(320);
});
