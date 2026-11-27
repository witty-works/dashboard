import AxeBuilder from '@axe-core/playwright';
import {test, expect, signedIn} from './fixtures.js';

// No WCAG 2.2 A or AA violation axe can detect, on the pages people use most.
// axe finds only part of them; a screen reader check before a release does
// the rest (README, deployment checklist).
const tags = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'];

async function violations(page) {
    // Embedded players (the academy's YouTube videos) are the provider's
    // markup, which this app cannot change.
    const results = await new AxeBuilder({page}).withTags(tags).exclude('iframe').analyze();
    return results.violations.map((v) => `${v.id}: ${v.help} (${v.nodes.map((n) => n.target.join(' ')).slice(0, 3).join(', ')})`);
}

test.describe('guest pages', () => {
    for (const path of ['/login', '/register', '/forgot-password']) {
        test(`${path} has no detectable violations`, async ({page}) => {
            await page.goto(path);
            expect(await violations(page)).toEqual([]);
        });
    }
});

test.describe('signed-in pages', () => {
    test.use({storageState: signedIn});

    for (const path of [
        '/en/user/profile',
        '/en/team/show',
        '/en/user/language/dictionary',
        '/en/team/language/dictionary',
        '/en/user/language/language-settings',
        '/en/team/language/customize-witty',
        '/en/user/academy',
    ]) {
        test(`${path} has no detectable violations`, async ({page}) => {
            await page.goto(path);
            expect(await violations(page)).toEqual([]);
        });
    }
});
