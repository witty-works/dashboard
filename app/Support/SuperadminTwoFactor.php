<?php

namespace App\Support;

use App\Models\User;

/**
 * Superadmins must use two-factor sign-in before they reach /admin or
 * impersonate anyone. Two things are checked:
 *
 * - the account has confirmed two-factor sign-in, and
 * - this session has seen a valid code from it: at the sign-in challenge,
 *   when it was confirmed on the profile page, or on the check page.
 *
 * The second covers sign-ins that never ask for a code: Office SSO (Microsoft
 * handles the second factor there) and "remember me" cookies.
 */
final class SuperadminTwoFactor
{
    /** Holds the id of the user whose code this session has seen. */
    public const SESSION_KEY = 'auth.two_factor_verified_user';

    /** Whether the requirement applies: the user is a superadmin. */
    public static function applies(?User $user): bool
    {
        return $user !== null && $user->can(config('admin.permission'));
    }

    /** A superadmin who has not confirmed two-factor sign-in yet. */
    public static function needsSetup(?User $user): bool
    {
        return self::applies($user) && ! $user->hasEnabledTwoFactorAuthentication();
    }

    /** A superadmin whose session has not seen a code. */
    public static function needsCode(?User $user): bool
    {
        return self::applies($user) && session(self::SESSION_KEY) !== $user->getAuthIdentifier();
    }

    /** Whether the user may use /admin and impersonate: met, or not a superadmin. */
    public static function passes(?User $user): bool
    {
        return ! self::needsSetup($user) && ! self::needsCode($user);
    }

    public static function markVerified(User $user): void
    {
        session()->put(self::SESSION_KEY, $user->getAuthIdentifier());
    }

    public static function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
