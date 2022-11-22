<?php

namespace App\Jobs;

use PostHog\PostHog;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncUserToPosthog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    public function __construct(User $user)
    {
        $this->id = $user->id;
    }

    public function handle()
    {
        $user = User::find($this->id);
        if (!$user instanceof User) {
            return -1;
        }

        if (!config('posthog.enabled')) {
            Log::debug("Posthog not enabled, otherwise update user: {$user->name} ({$user->id})");

            return 0;
        }

        PostHog::init(
            config('posthog.api_key'),
            ['host' => config('posthog.host'), 'debug' => config('posthog.debug')],
        );

        $properties = $user->getHubspotData();
        $properties['hubspot_id'] = $user->hubspot_id;
        $properties['$groups'] = [
            AppServiceProvider::POSTHOG_ORGANIZATION_TYPE => $user->posthogTeamId()
        ];

        $result = PostHog::identify([
            'distinctId' => $user->posthogId(),
            'properties' => $properties,
        ]);

        if (!$result) {
            return -1;
        }

        return 0;
    }
}
