<?php

namespace App\Jobs;

use App\Jobs\Concerns\RetriesThroughOutages;
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
    use RetriesThroughOutages;

    protected $id;
    protected $model;
    protected $force;
    protected $isDeleted;
    protected $groupKey;

    public function __construct($model, $force = false, $isDeleted = false)
    {
        $this->id = $model->id;
        $this->model = $model instanceof Team ? 'team' : 'user';
        $this->force = $force;
        $this->isDeleted = $isDeleted;
        // A sync run before a deletion commits would still see the user and
        // send their properties after PostHog deleted the person.
        $this->afterCommit();
        // The team is gone by the time a deletion's sync runs. With
        // POSTHOG_DASHBOARD_TEAM_ID set, the group is shared by every team.
        $this->groupKey = $model instanceof Team && config('posthog.dashboard_team_id_override') === null
            ? $model->posthogId()
            : null;
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
            // Deleted since the job was queued, for example by deleting their
            // account, which also queues syncs through the team events.
            // There is nothing left to send, and retrying cannot change that.
            return true;
        }

        $properties = $user->getUserData();

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
        if ($this->isDeleted) {
            return $this->groupKey === null ? true : $this->clearDeletedTeam();
        }

        $team = Team::find($this->id);
        if (!$team instanceof Team) {
            // Deleted since the job was queued, as when an account is deleted
            // with its own team. Nothing is left to send.
            return true;
        }

        $properties = [
            'is_deleted' => $this->isDeleted,
            'name' => $team->name,
            'owner' => $team->owner->posthogId(),
            'impersonate_url' => config('app.url') . '/impersonate/take/' . $team->owner->id,
            'users' => $team->getTotalUserCount(),
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

    /**
     * PostHog cannot delete a group, so a deleted team's group keeps its key
     * and loses the properties that name the team and its owner.
     */
    protected function clearDeletedTeam()
    {
        $properties = [
            'is_deleted' => true,
            'name' => null,
            'owner' => null,
            'impersonate_url' => null,
            'users' => 0,
        ];

        $result = PostHog::groupIdentify([
            'groupType' => PosthogHelper::POSTHOG_ORGANIZATION_TYPE,
            'groupKey' => $this->groupKey,
            'properties' => $properties,
        ]);

        return $result ? $properties : false;
    }
}
