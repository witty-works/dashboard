<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Logout;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;

/**
 * Revokes a user's OAuth tokens when they log out of the dashboard.
 *
 * Without this, "log out" only ends the browser session while the extension
 * keeps a working access token and a 30-day refresh token — which is not what
 * anyone means by logging out, least of all on a shared machine.
 */
class RevokeOAuthTokens
{
    public function __construct(
        protected TokenRepository $tokens,
        protected RefreshTokenRepository $refreshTokens
    ) {
    }

    public function handle(Logout $event): void
    {
        // Illuminate\Auth\Events\Logout also fires for programmatic logouts, and
        // one of those would do real damage here: OAuthController's Office SSO
        // handler calls Auth::logout() to clear whoever is in the session before
        // signing the Office user in. Revoking there would disconnect the
        // extension as a side effect of opening the Word add-in. Only an actual
        // POST to Fortify's logout route counts.
        if (! request()->routeIs('logout')) {
            return;
        }

        if (! $event->user instanceof User) {
            return;
        }

        // cursor() because a long-lived account can accumulate a lot of expired
        // rows, and this runs synchronously inside the logout request.
        foreach ($event->user->tokens()->where('revoked', false)->cursor() as $token) {
            $this->refreshTokens->revokeRefreshTokensByAccessTokenId($token->id);
            $this->tokens->revokeAccessToken($token->id);
        }
    }
}
