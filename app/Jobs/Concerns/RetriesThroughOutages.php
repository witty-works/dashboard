<?php

namespace App\Jobs\Concerns;

use DateTimeInterface;

/**
 * Keeps a job that calls the NLP API or PostHog trying through an outage of
 * hours: a backoff that grows to an hour, about 25 attempts in all, and a
 * failure for good (failed_jobs and the failed-job mail) only after a day.
 * A deleted account's settings, keys and events are removed even when the
 * service was down for a while.
 */
trait RetriesThroughOutages
{
    /** Seconds before each retry; the last one repeats. */
    public function backoff(): array
    {
        return [60, 300, 900, 1800, 3600];
    }

    /** Takes precedence over $tries: the job is tried until then. */
    public function retryUntil(): DateTimeInterface
    {
        return now()->addDay();
    }
}
