// @vitest-environment happy-dom
import {beforeAll, beforeEach, describe, expect, it, vi} from 'vitest';

describe('the edit buttons of the dictionary, ignored words and domains', () => {
    beforeAll(async () => {
        await import('./app.js');
    });

    beforeEach(() => {
        document.body.innerHTML = `
            <textarea id="term"></textarea>
            <table><tr><td><button type="button" data-scroll-to="term">Edit</button></td></tr></table>
        `;
        Element.prototype.scrollIntoView = vi.fn();
    });

    it('scroll to the field they fill', () => {
        document.querySelector('[data-scroll-to]').click();

        expect(Element.prototype.scrollIntoView).toHaveBeenCalledOnce();
    });

    // WCAG 2.4.3: a keyboard or screen reader user lands where the form is.
    it('move focus to the field', () => {
        document.querySelector('[data-scroll-to]').click();

        expect(document.activeElement).toBe(document.getElementById('term'));
    });
});
