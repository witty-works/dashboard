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

class SyncToPosthog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $model;
    protected $force;
    protected $isDeleted;

    public function __construct($model, $force = false, $isDeleted = false)
    {
        $this->id = $model->id;
        $this->model = $model instanceof Team ? 'team' : 'user';
        $this->force = $force;
        $this->isDeleted = $isDeleted;
    }

    public function handle()
    {
        if (!config('posthog.enabled')) {
            Log::debug("Posthog not enabled, otherwise update model {$this->model}, id {$this->id})");

            return true;
        }

        PostHog::init(
            config('posthog.api_key'),
            ['host' => config('posthog.host'), 'debug' => config('posthog.debug')],
        );

        switch ($this->model) {
            case 'team':
                $result = $this->handleTeam();
                break;
            case 'user':
                $result = $this->handleUser();
                break;
            default:
                throw new InvalidArgumentException("Invalid model type {$this->model} (must be either 'team' or 'user').");
        }

        if (!$result) {
            throw new InvalidArgumentException("{$this->model}, id {$this->id} could not be synced to Posthog.");
        }

        return $result;
    }

    public function handleUser()
    {
        $user = User::find($this->id);
        if (!$user instanceof User) {
            throw new InvalidArgumentException("User id '{$this->id} does not exist.");
        }

        $properties = $user->getHubspotData();
        $properties['hubspot_source'] = $user->hubspot_source;
        $properties['hubspot_company_id'] = $user->hubspot_company_id;
        $properties['hubspot_id'] = $user->hubspot_id;
        $properties['$groups'] = [
            PosthogHelper::POSTHOG_ORGANIZATION_TYPE => $user->posthogTeamId()
        ];

        $encodedProperties = json_encode($properties);
        if (!$this->force && $user->posthog_last_sync_data === $encodedProperties) {
            return true;
        }

        $result = PostHog::identify([
            'distinctId' => $user->posthogId(),
            'properties' => $properties,
        ]);

        if (!$result) {
            return false;
        }

        User::withoutTimestamps(function () use ($user, $encodedProperties) {
            $user->posthog_last_sync = now();
            $user->posthog_last_sync_data = $encodedProperties;
            $user->saveQuietly();
        });

        return $properties;
    }

    public function handleTeam()
    {
        $team = Team::find($this->id);
        if (!$team instanceof Team) {
            throw new InvalidArgumentException("Team id '{$this->id} does not exist.");
        }

        $properties = [
            'is_deleted' => $this->isDeleted,
            'name' => $team->name,
            'owner' => $team->owner->posthogId(),
            'impersonate_url' => config('app.url') . '/impersonate/take/' . $team->owner->id,
            'users' => $team->getTotalUserCount(),
            'stripe_plan' => $team->planId(),
        ];

        $encodedProperties = json_encode($properties);
        if (!$this->force && $team->posthog_last_sync_data === $encodedProperties) {
            return true;
        }

        $result = PostHog::groupIdentify([
            'groupType' => PosthogHelper::POSTHOG_ORGANIZATION_TYPE,
            'groupKey' => $team->posthogId(),
            'properties' => $properties,
        ]);

        if (!$result) {
            return false;
        }

        Team::withoutTimestamps(function () use ($team, $encodedProperties) {
            $team->posthog_last_sync = now();
            $team->posthog_last_sync_data = $encodedProperties;
            $team->saveQuietly();
        });

        return $properties;
    }
}
