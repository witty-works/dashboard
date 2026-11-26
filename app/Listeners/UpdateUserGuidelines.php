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
            dispatch(new SyncUserToNlpApi($user, 'high'));
        }
    }
}
