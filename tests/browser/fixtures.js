import {test as base, expect} from '@playwright/test';

// Every page fails its test on a console error or an uncaught exception,
// which includes anything the Content-Security-Policy blocks.
export const test = base.extend({
    page: async ({page}, use) => {
        const problems = [];
        page.on('console', (message) => {
            // Only the dashboard's own messages: an embedded player (the
            // academy's YouTube videos) reports its own policy issues.
            const from = message.location().url || '';
            if (message.type() === 'error' && (!from || from.startsWith(new URL(page.url() || 'http://127.0.0.1').origin))) {
                problems.push(`console: ${message.text()}`);
            }
        });
        page.on('pageerror', (error) => problems.push(`page error: ${error.message}`));
        await use(page);
        expect(problems, 'console errors and page errors').toEqual([]);
    },
});

export {expect};

export const signedIn = 'tests/browser/.auth/owner.json';
