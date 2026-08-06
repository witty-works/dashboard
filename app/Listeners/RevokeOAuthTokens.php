<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Logout;
use Laravel\Passport\Passport;

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
        // Refresh tokens are matched against *every* access token this user has,
        // not just the unrevoked ones. league/oauth2-server's refresh grant does
        // not look at the access token's revoked flag, so a refresh token whose
        // access token was revoked by some other path still mints new access
        // tokens for its full 30 days. Filtering the outer set would silently
        // leave those behind.
        //
        // Two bulk statements rather than a loop: this runs synchronously inside
        // the logout request, and an account that has been signing in for a year
        // has a lot of rows. The subquery keeps the token ids in the database
        // instead of loading them all into memory.
        Passport::refreshTokenModel()::query()
            ->whereIn('access_token_id', $event->user->tokens()->select('id'))
            ->where('revoked', false)
            ->update(['revoked' => true]);

        $event->user->tokens()
            ->where('revoked', false)
            ->update(['revoked' => true]);
    }
}
