// The write page: Witty's editor and a prompt, signed in with the dashboard's
// own OAuth tokens. The page is its own OAuth client (PKCE, no secret) and
// its redirect URI, so the user, already signed in to the dashboard, passes
// through the authorization endpoint without a consent screen.
//
// The tokens are kept in memory only: a reload signs in again, which costs
// one redirect. The PKCE verifier and state wait in sessionStorage for the
// one redirect, and are removed when it comes back.
import {mount} from '@witty-works/editor';

import {authorizeUrl, callbackCode, createPkce, exchangeCode, refreshTokens} from './write/oauth.js';
import {renderIssues, setEditorText, showReload, showStatus} from './write/render.js';
import {nextAction, refreshDelay} from './write/session.js';

const readJson = (id) => JSON.parse(document.getElementById(id).textContent);

const config = readJson('write-config');
const messages = readJson('write-messages');
const status = document.getElementById('write-status');
const promptForm = document.getElementById('write-prompt');
const promptText = document.getElementById('write-prompt-text');
const aiSuggestions = document.getElementById('write-ai');
const issues = document.getElementById('write-issues');

const PKCE_KEY = 'witty-write-pkce';

let tokens = null;
let handle = null;
let refreshTimer = null;
let refreshing = null;
let ended = false;

/** The session is over: the text stays, and the page says what to do. */
const end = (action) => {
    ended = true;
    clearTimeout(refreshTimer);
    promptForm.hidden = true;
    if (action === 'no-access') {
        handle?.editor?.setEditable?.(false);
        showStatus(status, messages.noAccess);
    } else {
        showReload(status, handle, messages.reload);
    }
};

/** One refresh at a time; resolves whether it worked. */
const refresh = () => {
    refreshing ??= refreshTokens(config, tokens.refreshToken)
        .then((fresh) => {
            tokens = fresh;
            handle?.setAccessToken(fresh.accessToken);
            schedule();
            return true;
        })
        .catch(() => false)
        .finally(() => {
            refreshing = null;
        });
    return refreshing;
};

const schedule = () => {
    clearTimeout(refreshTimer);
    refreshTimer = setTimeout(async () => {
        if (!(await refresh())) end(nextAction('refresh-failed', true));
    }, refreshDelay(tokens.expiresAt, Date.now()));
};

/**
 * A request to the NLP API with the access token. A refused token is
 * refreshed once (nextAction); null when the session has ended.
 */
const apiFetch = async (path, init = {}, {forbiddenIsAnAnswer = false} = {}) => {
    let refreshed = false;
    for (;;) {
        const response = await fetch(config.apiUrl + path, {
            ...init,
            headers: {
                Accept: 'application/json',
                ...init.headers,
                Authorization: `Bearer ${tokens.accessToken}`,
            },
        });
        const action = nextAction(response.status, refreshed, {forbiddenIsAnAnswer});
        if (action === 'none') return response;
        if (action === 'refresh') {
            refreshed = true;
            if (await refresh()) continue;
            end(nextAction('refresh-failed', true));
            return null;
        }
        end(action);
        return null;
    }
};

/**
 * Tokens from the callback, or a redirect to the authorization endpoint
 * (then null: the page is navigating away).
 */
const signIn = async () => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('code') || params.has('error')) {
        const stored = JSON.parse(sessionStorage.getItem(PKCE_KEY) ?? 'null');
        sessionStorage.removeItem(PKCE_KEY);
        // The code is single-use and must not stay in the address or history,
        // whether or not it is accepted.
        const {search} = window.location;
        window.history.replaceState(null, '', config.pageUrl);
        if (params.has('error')) throw new Error(`Sign-in refused: ${params.get('error')}`);
        const code = callbackCode({search}, stored);
        return exchangeCode(config, code, stored.verifier);
    }
    const pkce = await createPkce();
    sessionStorage.setItem(PKCE_KEY, JSON.stringify({verifier: pkce.verifier, state: pkce.state}));
    window.location.assign(authorizeUrl(config, pkce));
    return null;
};

/** Whether this user may use the language model, as the API reports it. */
const llmAllowed = async () => {
    if (!config.llmEnabled) return false;
    const response = await apiFetch('v2.0/features');
    if (!response?.ok) return false;
    const body = await response.json();
    return body?.features?.llm?.allowed === true;
};

// The editor reports a refused token itself; it gets the same rule, once
// per token.
let editorRefreshedFor = null;
const onStatus = async (next) => {
    if (next.state !== 'unauthorized' || ended) return;
    const action = nextAction(next.status, editorRefreshedFor === tokens.accessToken);
    if (action === 'refresh') {
        editorRefreshedFor = tokens.accessToken;
        if (!(await refresh())) end(nextAction('refresh-failed', true));
        return;
    }
    if (action !== 'none') end(action);
};

const rewrite = async (event) => {
    event.preventDefault();
    const prompt = promptText.value.trim();
    if (!prompt || ended) return;
    const button = promptForm.querySelector('button[type="submit"]');
    button.disabled = true;
    promptForm.setAttribute('aria-busy', 'true');
    showStatus(status, messages.rewriting);
    try {
        const response = await apiFetch('v1.0/write', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({prompt, text: handle.getText()}),
        }, {forbiddenIsAnAnswer: true});
        if (!response) return;
        if (response.status === 403) {
            // The team's LLM is off (perhaps since the page was opened): the
            // editor and the text stay, the prompt goes.
            promptForm.hidden = true;
            showStatus(status, messages.llmRefused);
            return;
        }
        if (!response.ok) {
            showStatus(status, messages.rewriteFailed);
            return;
        }
        const body = await response.json();
        const text = body.reviewed_response ?? body.initial_response;
        if (typeof text === 'string' && text !== '') setEditorText(handle, text);
        renderIssues(issues, body.check_results);
        showStatus(status, '');
    } catch (error) {
        showStatus(status, messages.rewriteFailed);
    } finally {
        button.disabled = false;
        promptForm.removeAttribute('aria-busy');
    }
};

const start = async () => {
    try {
        tokens = await signIn();
    } catch (error) {
        showStatus(status, messages.signInFailed);
        return;
    }
    if (!tokens) return;
    schedule();

    // The API says whether it lets this user in before any text is sent.
    const auth = await apiFetch('v2.0/auth', {method: 'POST'});
    if (!auth) return;
    if (!auth.ok) {
        showStatus(status, messages.signInFailed);
        return;
    }
    const llm = await llmAllowed();
    if (ended) return;

    handle = mount(document.getElementById('write-editor'), {
        endpoint: config.apiUrl,
        accessToken: tokens.accessToken,
        llmAlternatives: false,
        label: messages.editorLabel,
        onStatus,
    });

    if (llm) {
        promptForm.hidden = false;
        promptForm.addEventListener('submit', rewrite);
        aiSuggestions.addEventListener('change', () => {
            handle.updateSettings({llmAlternatives: aiSuggestions.checked});
        });
    }
};

start().catch(() => showStatus(status, messages.signInFailed));
