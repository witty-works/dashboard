<?php

namespace App\Jobs;

use App\Helpers\PosthogHelper;
use PostHog\PostHog;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;
use Spatie\RateLimitedMiddleware\RateLimited;

class SyncToPosthog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $model;
    protected $isDeleted;

    public function __construct($model, $isDeleted = false)
    {
        $this->id = $model->id;
        $this->model = $model instanceof Team ? 'team' : 'user';
        $this->isDeleted = $isDeleted;
    }

    public function retryUntil()
    {
        return now()->addHour(5);
    }

    public function middleware()
    {
        $rateLimitedMiddleware = new RateLimited(false);

        $rateLimitedMiddleware
            ->allow(config('posthog.rate.limit'))
            ->everySeconds(config('posthog.rate.interval_seconds'));

        return [$rateLimitedMiddleware];
    }

    public function handle()
    {
        if ($this->model === 'team') {
            $team = Team::find($this->id);
            return $this->handleTeam($team);
        }

        $user = User::find($this->id);
        return $this->handleUser($user);
    }

    public function handleUser($user)
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException("User id '{$this->id} does not exist.");
        }

        if (!config('posthog.enabled')) {
            Log::debug("Posthog not enabled, otherwise update user: {$user->name} ({$user->id})");

            return true;
        }

        PostHog::init(
            config('posthog.api_key'),
            ['host' => config('posthog.host'), 'debug' => config('posthog.debug')],
        );

        $properties = $user->getHubspotData();
        $properties['hubspot_source'] = $user->hubspot_source;
        $properties['hubspot_id'] = $user->hubspot_id;
        $properties['$groups'] = [
            PosthogHelper::POSTHOG_ORGANIZATION_TYPE => $user->posthogTeamId()
        ];

        $result = PostHog::identify([
            'distinctId' => $user->posthogId(),
            'properties' => $properties,
        ]);

        if (!$result) {
            throw new InvalidArgumentException("User id '{$this->id} could not be added to Posthog.");
        }

        return $properties;
    }

    public function handleTeam($team)
    {
        if (!$team instanceof Team) {
            throw new InvalidArgumentException("Team id '{$this->id} does not exist.");
        }

        if (!config('posthog.enabled')) {
            Log::debug("Posthog not enabled, otherwise update organization: {$team->name} ({$team->id})");

            return true;
        }

        PostHog::init(
            config('posthog.api_key'),
            ['host' => config('posthog.host'), 'debug' => config('posthog.debug')],
        );

        $properties = [
            'is_deleted' => $this->isDeleted,
            'name' => $team->name,
            'owner' => $team->owner->posthogId(),
            'impersonate_url' => config('app.url') . '/impersonate/take/' . $team->owner->id,
            'users' => $team->getTotalUserCount(),
            'stripe_plan' => $team->planId(),
        ];

        $result = PostHog::groupIdentify([
            'groupType' => PosthogHelper::POSTHOG_ORGANIZATION_TYPE,
            'groupKey' => $team->posthogId(),
            'properties' => $properties,
        ]);

        if (!$result) {
            throw new InvalidArgumentException("Team id '{$this->id} could not be added to Posthog.");
        }

        return $properties;
    }
}
