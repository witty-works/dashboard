<?php

namespace App\Listeners;

use App\Listeners\SourceTrait;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Spatie\SlackAlerts\Facades\SlackAlert;

class TeamMemberAddedSlackAlert
{
    use SourceTrait;

    public function handle(TeamMemberAdded $event)
    {
        $subscription = $event->team->subscription();
        if (!$subscription) {
            $message = sprintf(
                " - A new team member '%s' was added to '%s' (%s), total count is now at %s via %s",
                $event->user->email,
                $event->team->name,
                $event->team->owner->email,
                $event->team->getTotalUserCount(),
                $this->getSource($event->user),
            );

            SlackAlert::message(getenv('PLATFORM_ENVIRONMENT') . $message);
        }
    }
}
