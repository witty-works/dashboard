// @vitest-environment happy-dom
import {beforeAll, describe, expect, it, vi} from 'vitest';

// The analytics page's inline script uses Chart and moment as globals. They
// come from the app's own bundle, not from a third-party CDN.
describe('the analytics bundle', () => {
    beforeAll(async () => {
        document.documentElement.lang = 'de';
        await import('./analytics.js');
    });

    it('provides Chart.js 2, whose API the page uses', () => {
        expect(typeof window.Chart).toBe('function');
        expect(window.Chart.defaults.global).toBeDefined();
    });

    it('registers the data labels plugin', () => {
        expect(window.Chart.plugins.getAll().map((plugin) => plugin.id)).toContain('datalabels');
    });

    it('provides moment in the page language', () => {
        expect(window.moment.locale()).toBe('de');
        expect(window.moment('2026-01-15').format('MMMM')).toBe('Januar');
    });

    it('has the French locale too', () => {
        expect(window.moment('2026-01-15').locale('fr').format('MMMM')).toBe('janvier');
    });
});

describe('the analytics bundle on an English page', () => {
    beforeAll(async () => {
        vi.resetModules();
        document.documentElement.lang = 'en';
        await import('./analytics.js');
    });

    it('keeps moment in English, not the last locale file loaded', () => {
        expect(window.moment.locale()).toBe('en');
        expect(window.moment('2026-01-15').format('MMMM')).toBe('January');
    });
});
