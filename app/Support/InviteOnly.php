<?php

namespace App\Support;

use App\Models\TeamInvitation;

/**
 * INVITE_ONLY: nobody signs up without an invitation, and superadmins set up
 * the teams. Off, sign-up and team creation work as before.
 */
class InviteOnly
{
    public static function enabled(): bool
    {
        return (bool) config('auth.invite_only');
    }

    /** Whether an account may be created for this address. */
    public static function allowsSignUp(?string $email): bool
    {
        if (! self::enabled()) {
            return true;
        }

        return $email !== null && $email !== ''
            && TeamInvitation::whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->exists();
    }
}
