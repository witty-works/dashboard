<?php

namespace App\Listeners;

use App\Events\AbstractSubscription;
use App\Helpers\PosthogHelper;
use App\Jobs\SendEventToPosthog;
use App\Jobs\SyncToPosthog;
use Laravel\Jetstream\Events\TeamMemberAdded;

class PostHogUpdateUser
{
    public function handle($event)
    {
        if (!empty($event->user)) {
            dispatch(new SyncToPosthog($event->user));
        }

        if (!empty($event->team)) {
            dispatch(new SyncToPosthog($event->team->owner));

            if ($event instanceof AbstractSubscription) {
                foreach ($event->team->users as $user) {
                    dispatch(new SyncToPosthog($user));
                }
            }
        }

        if ($event instanceof TeamMemberAdded) {
            $job = new SendEventToPosthog(
                $event->user,
                PosthogHelper::JOINED_TEAM,
                [
                    'team_id' => $event->team->id,
                ],
            );

            dispatch($job);

            $job = new SendEventToPosthog(
                $event->team->owner,
                PosthogHelper::ADDED_TEAM_MEMBER,
                [
                    'user_id' => $event->user->id,
                ],
            );

            dispatch($job);
        }
    }
}
