// What the page does when the API refuses its token. The rule is the
// editor's (its README): refresh once on 401 or 403, since an API before
// 4.0.0 answers 403 for an expired token, too; refused again with the fresh
// token, a 401 means the session is over and a 403 that the user is not let
// in. A refresh that fails ends the session as well.

/** `status` is the HTTP status, or 'refresh-failed'. */
export function nextAction(status, refreshedAlready, {forbiddenIsAnAnswer = false} = {}) {
    if (status === 'refresh-failed') return 'reload';
    if (status !== 401 && status !== 403) return 'none';
    if (!refreshedAlready) return 'refresh';
    // A request the API answers with 403 for what it asks, not for who asks
    // (the rewrite, for a team without the LLM): the caller shows that.
    if (status === 403 && forbiddenIsAnAnswer) return 'none';
    return status === 403 ? 'no-access' : 'reload';
}

// Refreshed this long before the access token expires.
const REFRESH_MARGIN_MS = 2 * 60 * 1000;

/** Milliseconds until the token should be refreshed; never negative. */
export function refreshDelay(expiresAt, now) {
    return Math.max(0, expiresAt - REFRESH_MARGIN_MS - now);
}
