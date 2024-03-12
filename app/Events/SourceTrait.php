<?php

namespace App\Events;

use App\Models\User;

trait SourceTrait
{
    protected function getSource(User $user)
    {
        foreach ($user->connectedAccounts() as $connectedAccount) {
            return $connectedAccount->provider;
        }

        return 'unknown';
    }
}
