<?php

namespace App\Listeners;

use App\Support\SuperadminTwoFactor;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEvent;

/**
 * Notes in the session that it has seen a valid code: at the sign-in
 * challenge or when two-factor sign-in was confirmed on the profile page.
 * Superadmins need that for /admin and impersonation (SuperadminTwoFactor).
 * Turning two-factor sign-in off removes the note.
 */
class TrackTwoFactorInSession
{
    public function handle(TwoFactorAuthenticationEvent $event): void
    {
        if ($event instanceof TwoFactorAuthenticationDisabled) {
            if (session(SuperadminTwoFactor::SESSION_KEY) === $event->user->getAuthIdentifier()) {
                SuperadminTwoFactor::forget();
            }

            return;
        }

        // A new session id, as the sign-in challenge starts one: an id someone
        // learned before is not raised to the level that opens /admin.
        if ($event instanceof TwoFactorAuthenticationConfirmed && session()->isStarted()) {
            session()->regenerate();
        }

        SuperadminTwoFactor::markVerified($event->user);
    }
}
