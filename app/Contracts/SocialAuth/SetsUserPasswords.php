<?php

namespace App\Contracts\SocialAuth;

interface SetsUserPasswords
{
    /**
     * Validate and set the user's password.
     */
    public function set(mixed $user, array $input): void;
}
