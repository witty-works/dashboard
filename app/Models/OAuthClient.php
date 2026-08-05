<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Client as PassportClient;

/**
 * Passport's OAuth client, extended so that first-party clients can skip the
 * consent screen.
 */
class OAuthClient extends PassportClient
{
    /**
     * Passport always returns false here, which means every client — including
     * our own browser extension — triggers the "…is requesting permission to
     * access your account" interstitial. For a first-party client that prompt is
     * noise: the user just signed in to this application in order to connect the
     * extension, and denying is indistinguishable from cancelling the login.
     *
     * Clients absent from the configured list keep the prompt. Passport's own
     * firstParty() is not usable for this — it means "personal access or
     * password grant client", and the extension is neither.
     *
     * The $user and $scopes arguments are part of Passport 13's signature and
     * are deliberately unused: membership of the configured list is a property
     * of the client, not of who is signing in or what they are asking for. We
     * request no scopes at all.
     *
     * @param  \Laravel\Passport\Scope[]  $scopes
     */
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        return in_array(
            (string) $this->getKey(),
            array_map('strval', config('passport.first_party_clients', [])),
            true
        );
    }
}
