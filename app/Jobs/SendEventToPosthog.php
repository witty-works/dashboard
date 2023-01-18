<?php

namespace App\Jobs;

use App\Helpers\PosthogHelper;
use App\Models\Team;
use PostHog\PostHog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;

class SendEventToPosthog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $event;
    protected $properties;

    public function __construct(User $user, $event, $properties, $isEdit, Team $team = null)
    {
        $this->id = $user->id;
        $this->event = $event;
        $this->properties = $properties;
        $this->properties['is_edit'] = $isEdit;
        if ($team) {
            $this->properties['$group'] = [
                PosthogHelper::POSTHOG_ORGANIZATION_TYPE => $team->posthogId()
            ];
        }
    }

    public function handle()
    {
        $user = User::find($this->id);
        if (!$user instanceof User) {
            throw new InvalidArgumentException("User id '{$this->id} does not exist.");
        }

        if (!config('posthog.enabled')) {
            Log::debug("Posthog not enabled, otherwise update user: {$user->name} ({$user->id})");

            return 0;
        }

        PostHog::init(
            config('posthog.api_key'),
            ['host' => config('posthog.host'), 'debug' => config('posthog.debug')],
        );

        $data = [
            'distinctId' => $user->posthogId(),
            'event' => $this->event,
            'properties' => $this->properties,
        ];

        $result = PostHog::capture($data);
        dump($data);
        if (!$result) {
            throw new InvalidArgumentException("User id '{$this->id} could not be added to Posthog.");
        }

        return 0;
    }
}
