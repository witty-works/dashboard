<?php

namespace Tests\Concerns;

use App\Models\User;
use App\Support\SuperadminTwoFactor;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;

/**
 * Two-factor sign-in for tests: an account with confirmed 2FA, the code an
 * authenticator app would show, and a superadmin who may use /admin.
 */
trait UsesTwoFactor
{
    /** Gives the account confirmed two-factor sign-in; returns the TOTP secret. */
    protected function confirmTwoFactor(User $user, array $recoveryCodes = ['recovery-code-one', 'recovery-code-two']): string
    {
        $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => Fortify::currentEncrypter()->encrypt($secret),
            'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $secret;
    }

    /** The code an authenticator app shows for the secret right now. */
    protected function otp(string $secret): string
    {
        return app(Google2FA::class)->getCurrentOtp($secret);
    }

    /** A superadmin with confirmed two-factor sign-in. */
    protected function superadminWithTwoFactor(User $user): User
    {
        if (! $user->hasRole(config('admin.role'))) {
            $user->assignRole(config('admin.role'));
        }
        $this->confirmTwoFactor($user);

        return $user->refresh();
    }

    /** Notes that this session has seen the user's code, as after the sign-in challenge. */
    protected function verifyTwoFactorSession(User $user): void
    {
        session()->put(SuperadminTwoFactor::SESSION_KEY, $user->id);
    }

    /**
     * A superadmin who has set up two-factor sign-in and entered a code in
     * this session: what /admin and impersonation require. The session holds
     * one user; with several superadmins, verify the acting one last.
     */
    protected function verifiedSuperadmin(User $user): User
    {
        $user = $this->superadminWithTwoFactor($user);
        $this->verifyTwoFactorSession($user);

        return $user;
    }
}
