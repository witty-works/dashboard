// @vitest-environment happy-dom
import {afterEach, describe, expect, it, vi} from 'vitest';

const init = vi.fn();
vi.mock('@sentry/browser', () => ({init}));

// The browser reports the release the server reports and the release
// workflow creates (docs/sentry.md): the bare version, so its errors meet the
// release's commits.
describe('the browser Sentry release', () => {
    afterEach(() => {
        init.mockClear();
        vi.resetModules();
        delete window.sentry_dsn;
        delete window.sentry_release;
    });

    const start = async (release) => {
        window.sentry_dsn = 'https://key@o0.ingest.sentry.io/0';
        window.sentry_release = release;
        await import('./app.js');
        expect(init).toHaveBeenCalledOnce();
        return init.mock.calls[0][0];
    };

    it('is the bare version', async () => {
        expect((await start('2.0.0-rc1')).release).toBe('2.0.0-rc1');
    });

    it('is left out without a version', async () => {
        expect((await start(null)).release).toBeUndefined();
        init.mockClear();
        vi.resetModules();
        expect((await start('')).release).toBeUndefined();
    });
});
