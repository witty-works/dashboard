<?php

namespace App\Jobs;

use PostHog\PostHog;
use App\Models\Team;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;

class SyncOrganizationToPosthog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $isDeleted;

    public function __construct(Team $team, $isDeleted = false)
    {
        $this->id = $team->id;
        $this->isDeleted = $isDeleted;
    }

    public function handle()
    {
        $team = Team::find($this->id);
        if (!$team instanceof Team) {
            throw new InvalidArgumentException("Team id '{$this->id} does not exist.");
        }

        if (!config('posthog.enabled')) {
            Log::debug("Posthog not enabled, otherwise update organization: {$team->name} ({$team->id})");

            return 0;
        }

        PostHog::init(
            config('posthog.api_key'),
            ['host' => config('posthog.host'), 'debug' => config('posthog.debug')],
        );

        $result = PostHog::groupIdentify([
            'groupType' => AppServiceProvider::POSTHOG_ORGANIZATION_TYPE,
            'groupKey' => $team->posthogId(),
            'properties' => [
                'is_deleted' => $this->isDeleted,
                'name' => $team->name,
                'owner' => $team->owner->posthogId(),
                'impersonate_url' => config('app.url') . '/impersonate/take/' . $team->owner->id,
                'users' => $team->getTotalUserCount(),
                'stripe_plan' => $team->planId(),
            ]
        ]);

        if (!$result) {
            throw new InvalidArgumentException("Team id '{$this->id} could not be added to Posthog.");
        }

        return 0;
    }
}
