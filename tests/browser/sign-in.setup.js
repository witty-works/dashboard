import {test as setup, expect} from '@playwright/test';
import {signedIn} from './fixtures.js';

// Signs in once through the login form (BrowserTestSeeder's owner) and keeps
// the session for the tests that need it.
setup('sign in', async ({page}) => {
    await page.goto('/login');
    await page.getByLabel(/email/i).fill('browser@example.org');
    await page.getByLabel(/password/i).fill('browser-test-password');
    await page.getByRole('button', {name: /^(log ?in|sign in)$/i}).click();
    await expect(page).not.toHaveURL(/login/);
    await page.context().storageState({path: signedIn});
});
