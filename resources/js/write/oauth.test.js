import {afterEach, describe, expect, it, vi} from 'vitest';

import {
    authorizeUrl,
    callbackCode,
    challengeFor,
    createPkce,
    exchangeCode,
    refreshTokens,
} from './oauth.js';

const config = {
    clientId: 'editor-client',
    authorizeUrl: 'https://dashboard.example/oauth/authorize',
    tokenUrl: 'https://dashboard.example/oauth/token',
    redirectUri: 'https://dashboard.example/write',
};

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('PKCE', () => {
    it('challenge follows RFC 7636', async () => {
        // The example in RFC 7636, appendix B.
        expect(await challengeFor('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk')).toBe(
            'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
        );
    });

    it('creates a base64url verifier of 43 to 128 characters and its challenge', async () => {
        const pkce = await createPkce();
        expect(pkce.verifier).toMatch(/^[A-Za-z0-9_-]{43,128}$/);
        expect(pkce.challenge).toBe(await challengeFor(pkce.verifier));
        expect(pkce.state).toMatch(/^[A-Za-z0-9_-]{16,}$/);
    });
});

describe('authorizeUrl', () => {
    it('asks for a code with S256', () => {
        const url = new URL(authorizeUrl(config, {challenge: 'c', state: 's'}));
        expect(url.origin + url.pathname).toBe(config.authorizeUrl);
        expect(Object.fromEntries(url.searchParams)).toEqual({
            response_type: 'code',
            client_id: 'editor-client',
            redirect_uri: config.redirectUri,
            code_challenge_method: 'S256',
            code_challenge: 'c',
            state: 's',
        });
    });
});

const tokenAnswer = () =>
    new Response(
        JSON.stringify({access_token: 'a', refresh_token: 'r', expires_in: 600}),
        {status: 200, headers: {'Content-Type': 'application/json'}},
    );

describe('token requests', () => {
    it('exchangeCode posts the verifier', async () => {
        const fetch = vi.fn(async () => tokenAnswer());
        vi.stubGlobal('fetch', fetch);
        vi.spyOn(Date, 'now').mockReturnValue(1000);

        expect(await exchangeCode(config, 'the-code', 'the-verifier')).toEqual({
            accessToken: 'a',
            refreshToken: 'r',
            expiresAt: 1000 + 600 * 1000,
        });
        const [url, init] = fetch.mock.calls[0];
        expect(url).toBe(config.tokenUrl);
        expect(init.method).toBe('POST');
        expect(Object.fromEntries(new URLSearchParams(init.body))).toEqual({
            grant_type: 'authorization_code',
            code: 'the-code',
            code_verifier: 'the-verifier',
            client_id: 'editor-client',
            redirect_uri: config.redirectUri,
        });
    });

    it('refreshTokens posts the refresh token', async () => {
        const fetch = vi.fn(async () => tokenAnswer());
        vi.stubGlobal('fetch', fetch);

        await refreshTokens(config, 'old-refresh');
        const [, init] = fetch.mock.calls[0];
        expect(Object.fromEntries(new URLSearchParams(init.body))).toEqual({
            grant_type: 'refresh_token',
            refresh_token: 'old-refresh',
            client_id: 'editor-client',
        });
    });

    it('rejects a refused token request', async () => {
        vi.stubGlobal('fetch', vi.fn(async () => new Response('{}', {status: 400})));
        await expect(refreshTokens(config, 'r')).rejects.toThrow();
    });
});

describe('callbackCode', () => {
    it('returns the code when the state matches', () => {
        const location = {search: '?code=abc&state=s1'};
        expect(callbackCode(location, {state: 's1'})).toBe('abc');
    });

    it('test_callback_with_wrong_state_is_rejected', () => {
        expect(() => callbackCode({search: '?code=abc&state=other'}, {state: 's1'})).toThrow();
        expect(() => callbackCode({search: '?code=abc'}, {state: 's1'})).toThrow();
        expect(() => callbackCode({search: '?code=abc&state=s1'}, {state: null})).toThrow();
    });
});
