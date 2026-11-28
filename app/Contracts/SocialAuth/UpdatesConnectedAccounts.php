<?php

namespace App\Contracts\SocialAuth;

use App\Models\ConnectedAccount;
use Laravel\Socialite\Contracts\User;

interface UpdatesConnectedAccounts
{
    /**
     * Update a given connected account.
     */
    public function update(mixed $user, ConnectedAccount $connectedAccount, string $provider, User $providerUser): ConnectedAccount;
}
