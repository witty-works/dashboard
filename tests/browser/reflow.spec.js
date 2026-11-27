import {test, expect, signedIn} from './fixtures.js';

// At 320px nothing scrolls sideways (WCAG 1.4.10).
test.use({viewport: {width: 320, height: 800}});

test('the login page fits 320px', async ({page}) => {
    await page.goto('/login');
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(320);
});

test.describe('signed in', () => {
    test.use({storageState: signedIn});

    for (const path of ['/en/user/profile', '/en/user/language/language-settings', '/en/user/language/dictionary']) {
        test(`${path} fits 320px`, async ({page}) => {
            await page.goto(path);
            expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(320);
        });
    }
});
