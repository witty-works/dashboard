<?php

namespace App\Listeners;

use App\Events\UserDeleted;
use App\Jobs\DeleteUserFromNlpApi;
use App\Jobs\RevokeApiKeysInNlpApi;
use App\Jobs\SyncUserToNlpApi;

class UpdateUserGuidelines
{
    public function handle($event)
    {
        $user = $event->user;

        if ($event instanceof UserDeleted) {
            dispatch(new DeleteUserFromNlpApi($user, 'medium'));
            dispatch(new RevokeApiKeysInNlpApi($user->email, null));
        } else {
            $old = $user->getOriginal('email');
            if ($user->wasChanged('email') && strtolower((string) $old) !== strtolower($user->email)) {
                // However the address changed: the NLP API knows people by
                // address, and the new one is not verified yet, so keys are
                // not moved to it. The person makes new ones once it is.
                dispatch(new DeleteUserFromNlpApi($old));
                dispatch(new RevokeApiKeysInNlpApi($old, null));
            }
            dispatch(new SyncUserToNlpApi($user, 'high'));
        }
    }
}
