import {test, expect, signedIn} from './fixtures.js';

test.use({storageState: signedIn});

test('the skip link is the first stop, visible, and leads to the main content', async ({page}) => {
    await page.goto('/en/user/profile');
    await page.keyboard.press('Tab');
    const link = page.locator('a.skip-link');
    await expect(link).toBeFocused();
    // Visible means on top: nothing, the fixed sidebar included, covers it.
    const onTop = await link.evaluate((el) => {
        const box = el.getBoundingClientRect();
        return document.elementFromPoint(box.left + box.width / 2, box.top + box.height / 2) === el;
    });
    expect(onTop).toBe(true);
    await page.keyboard.press('Enter');
    await expect(page).toHaveURL(/#maincontent$/);
});

test('editing a dictionary entry moves focus to its field', async ({page}) => {
    await page.goto('/en/user/language/dictionary');
    await page.locator('[data-scroll-to]').first().click();
    await expect(page.locator('#term')).toBeFocused();
});
