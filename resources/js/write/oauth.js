// The write page signs in with the dashboard's own OAuth server: the
// authorization code flow with PKCE (RFC 7636) for a public client, as the
// browser extension does. No client secret; the verifier proves that the
// page that asked for the code is the one exchanging it.

const base64url = (bytes) =>
    btoa(String.fromCharCode(...new Uint8Array(bytes)))
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=+$/, '');

const randomString = (byteCount) => {
    const bytes = new Uint8Array(byteCount);
    crypto.getRandomValues(bytes);
    return base64url(bytes);
};

/** The S256 challenge for a verifier. */
export async function challengeFor(verifier) {
    const digest = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(verifier));
    return base64url(digest);
}

/** A verifier (43 characters of base64url), its challenge, and a state. */
export async function createPkce() {
    const verifier = randomString(32);
    return {verifier, challenge: await challengeFor(verifier), state: randomString(16)};
}

export function authorizeUrl(config, pkce) {
    const url = new URL(config.authorizeUrl);
    url.search = new URLSearchParams({
        response_type: 'code',
        client_id: config.clientId,
        redirect_uri: config.redirectUri,
        code_challenge_method: 'S256',
        code_challenge: pkce.challenge,
        state: pkce.state,
    }).toString();
    return url.toString();
}

const requestTokens = async (config, fields) => {
    const response = await fetch(config.tokenUrl, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({...fields, client_id: config.clientId}).toString(),
    });
    if (!response.ok) {
        throw new Error(`Token request refused (${response.status})`);
    }
    const body = await response.json();
    return {
        accessToken: body.access_token,
        refreshToken: body.refresh_token,
        expiresAt: Date.now() + Number(body.expires_in) * 1000,
    };
};

export function exchangeCode(config, code, verifier) {
    return requestTokens(config, {
        grant_type: 'authorization_code',
        code,
        code_verifier: verifier,
        redirect_uri: config.redirectUri,
    });
}

export function refreshTokens(config, refreshToken) {
    return requestTokens(config, {grant_type: 'refresh_token', refresh_token: refreshToken});
}

/**
 * The code of an OAuth callback. Throws unless its state is the one this
 * page stored before redirecting: otherwise another site could make the page
 * sign in with that site's code.
 */
export function callbackCode(location, stored) {
    const params = new URLSearchParams(location.search);
    const code = params.get('code');
    const state = params.get('state');
    if (!code || !state || !stored?.state || state !== stored.state) {
        throw new Error('The sign-in answer does not belong to this page');
    }
    return code;
}
