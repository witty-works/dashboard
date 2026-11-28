import {describe, expect, it} from 'vitest';

import {nextAction, refreshDelay} from './session.js';

describe('nextAction', () => {
    it('refreshes once on 401 or 403', () => {
        // An API before 4.0.0 answers 403 for an expired token, too.
        expect(nextAction(401, false)).toBe('refresh');
        expect(nextAction(403, false)).toBe('refresh');
    });

    it('401 refreshes once, then asks to reload', () => {
        expect(nextAction(401, true)).toBe('reload');
    });

    it('test_next_action_on_403_is_no_access', () => {
        // Refused again with a fresh token: the user is not let in.
        expect(nextAction(403, true)).toBe('no-access');
    });

    it('test_next_action_after_failed_refresh_is_reload', () => {
        expect(nextAction('refresh-failed', true)).toBe('reload');
    });

    it('a 403 that answers the request itself does not end the session', () => {
        // The rewrite: refused for this user's team, not for their token.
        expect(nextAction(403, false, {forbiddenIsAnAnswer: true})).toBe('refresh');
        expect(nextAction(403, true, {forbiddenIsAnAnswer: true})).toBe('none');
        expect(nextAction(401, true, {forbiddenIsAnAnswer: true})).toBe('reload');
    });

    it('does nothing for an answer that is not a refusal', () => {
        expect(nextAction(200, false)).toBe('none');
        expect(nextAction(503, false)).toBe('none');
    });
});

describe('refreshDelay', () => {
    it('refreshDelay is two minutes before expiry and never negative', () => {
        expect(refreshDelay(10 * 60 * 1000, 0)).toBe(8 * 60 * 1000);
        expect(refreshDelay(60 * 1000, 0)).toBe(0);
        expect(refreshDelay(0, 5000)).toBe(0);
    });
});
