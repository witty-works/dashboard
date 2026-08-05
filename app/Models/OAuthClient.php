<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\Scope;

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
     * Two conditions, not one. Being first-party says *who* is asking; it says
     * nothing about *what* they are asking for. Skipping consent on the strength
     * of identity alone means that the day someone introduces scopes, a
     * first-party client silently receives whatever it puts in the request —
     * which is precisely the grant the consent screen exists to authorise.
     *
     * So a client skips the prompt only for scopes listed in
     * `passport.first_party_scopes`. That list is empty by default and the
     * application defines no scopes today, so an ordinary request asks for
     * nothing, `array_diff` is empty, and the extension skips the prompt exactly
     * as before. Introduce a scope and requests carrying it stop skipping until
     * it is added to the list — a deliberate, reviewable config change instead of
     * a silent widening.
     *
     * Returning false is not the same as forcing a prompt: Passport falls back to
     * hasGrantedScopes(), so a user who has already approved these scopes is
     * still let through without one.
     *
     * Passport's own firstParty() is not usable for any of this — it means
     * "personal access or password grant client", and the extension is neither.
     *
     * @param  \Laravel\Passport\Scope[]  $scopes
     */
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        if (! $this->isFirstParty()) {
            return false;
        }

        $requested = array_map(fn (Scope $scope): string => $scope->id, $scopes);

        return array_diff($requested, $this->autoGrantedScopes()) === [];
    }

    /**
     * Whether this client is one of ours, per PASSPORT_FIRST_PARTY_CLIENTS.
     */
    public function isFirstParty(): bool
    {
        return in_array(
            (string) $this->getKey(),
            array_map('strval', config('passport.first_party_clients', [])),
            true
        );
    }

    /**
     * Scopes a first-party client may receive without an explicit prompt.
     *
     * @return string[]
     */
    protected function autoGrantedScopes(): array
    {
        return array_map('strval', config('passport.first_party_scopes', []));
    }
}
