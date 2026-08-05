<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Logout;

/**
 * Revokes a user's OAuth tokens when they log out of the dashboard.
 *
 * Without this, "log out" only ends the browser session while the extension
 * keeps a working access token and a 30-day refresh token — which is not what
 * anyone means by logging out, least of all on a shared machine.
 */
class RevokeOAuthTokens
{
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

        // Revoked through the models rather than a repository: Passport 13
        // deleted RefreshTokenRepository and reduced TokenRepository to lookups.
        //
        // cursor() because a long-lived account can accumulate a lot of expired
        // rows, and this runs synchronously inside the logout request. The
        // refresh token goes first — it is the one that could mint a new access
        // token, so if this loop is interrupted the surviving half is the
        // short-lived one.
        foreach ($event->user->tokens()->where('revoked', false)->cursor() as $token) {
            $token->refreshToken?->revoke();
            $token->revoke();
        }
    }
}
