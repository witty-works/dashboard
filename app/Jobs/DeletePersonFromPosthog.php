<?php

namespace App\Jobs;

use App\Helpers\PosthogHelper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Deletes a deleted user's person in PostHog, with their events and session
 * recordings. PostHog removes the events in the background.
 */
class DeletePersonFromPosthog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 5;

    public $backoff = [60, 300, 900, 3600];

    protected string $distinctId;

    protected bool $overridden;

    public function __construct(User $user)
    {
        $this->distinctId = $user->posthogId();
        // With POSTHOG_DASHBOARD_USER_ID set, every user reports as that one
        // person, so deleting it would delete someone else's data.
        $this->overridden = config('posthog.dashboard_user_id_override') !== null;
        // Only once the account deletion is saved.
        $this->afterCommit();
    }

    public function handle(): void
    {
        if (!config('posthog.enabled')) {
            return;
        }

        if ($this->overridden) {
            Log::warning("PostHog person {$this->distinctId} was not deleted: POSTHOG_DASHBOARD_USER_ID is set, so it is shared by every user.");

            return;
        }

        if (empty(config('posthog.personal_api_key')) || empty(config('posthog.project_id'))) {
            Log::error("PostHog person {$this->distinctId} was not deleted: set POSTHOG_PERSONAL_API_KEY and POSTHOG_PROJECT_ID, then delete the person and their events by hand.");

            return;
        }

        try {
            Http::withToken(config('posthog.personal_api_key'))
                ->acceptJson()
                ->timeout(30)
                ->post(PosthogHelper::getPersonsBulkDeleteUrl(), [
                    'distinct_ids' => [$this->distinctId],
                    'delete_events' => true,
                    'delete_recordings' => true,
                ])
                ->throw();
        } catch (Throwable $e) {
            // A real queue retries outages and rate limits. The inline queue
            // cannot, and the account is already deleted, so the user gets no
            // error page for it. A refused key will not get better by waiting.
            $status = $e instanceof RequestException ? $e->response->status() : null;
            $retryable = $status === null || $status === 429 || $status >= 500;
            if ($retryable && !$this->job instanceof SyncJob && $this->attempts() < $this->tries) {
                throw $e;
            }

            $this->failed($e);
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error("PostHog person {$this->distinctId} could not be deleted; delete the person and their events by hand: " . $e->getMessage());
    }
}
