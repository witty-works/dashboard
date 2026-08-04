<?php

namespace App\Models;

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
     * @return bool
     */
    public function skipsAuthorization()
    {
        return in_array(
            (string) $this->getKey(),
            array_map('strval', config('passport.first_party_clients', [])),
            true
        );
    }
}
