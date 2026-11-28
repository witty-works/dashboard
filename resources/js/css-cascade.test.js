import {readFileSync} from 'node:fs';
import {fileURLToPath} from 'node:url';
import postcss from 'postcss';
import tailwindcss from 'tailwindcss';
import {beforeAll, describe, expect, it} from 'vitest';
import config from '../../tailwind.config.js';

const cssPath = fileURLToPath(new URL('../css/app.css', import.meta.url));

// Builds resources/css/app.css with Tailwind for one piece of markup and
// returns the rules in the order the browser applies them.
async function build(html) {
    const result = await postcss([tailwindcss({...config, content: [{raw: html, extension: 'html'}]})])
        .process(readFileSync(cssPath, 'utf8'), {from: cssPath});
    const rules = [];
    result.root.walkRules((rule) => {
        rule.walkDecls('color', (decl) => {
            rules.push({selector: rule.selector, value: decl.value, important: decl.important});
        });
    });

    return rules;
}

// The colour that wins for an element with all the given classes, by source
// order alone: every rule here is a single class.
function winningColor(rules, classes) {
    const applying = rules.filter((rule) => rule.selector.split(',').map((part) => part.trim())
        .some((part) => classes.includes(part.replace(/^\./, '').replace(/\\/g, ''))));
    const important = applying.filter((rule) => rule.important);

    return (important.length ? important : applying).at(-1)?.value;
}

describe('the text classes of app.css', () => {
    let rules;

    beforeAll(async () => {
        rules = await build('<button class="text-white text-gray-700 lato-paragraph-text-p lato-small-paragraph-title-h4 proficiency-level-p">');
    }, 30000);

    // The danger button is white on red; the Lato class made its text black.
    it('lets a Tailwind colour utility override the Lato text colour', () => {
        expect(winningColor(rules, ['lato-paragraph-text-p', 'text-white'])).toMatch(/rgb\(255 255 255|#fff/);
        expect(winningColor(rules, ['lato-small-paragraph-title-h4', 'text-white'])).toMatch(/rgb\(255 255 255|#fff/);
        expect(winningColor(rules, ['proficiency-level-p', 'text-white'])).toMatch(/rgb\(255 255 255|#fff/);
    });

    it('keeps black as the Lato text colour otherwise', () => {
        expect(winningColor(rules, ['lato-paragraph-text-p'])).toBe('#000000');
    });
});
