<?php

namespace App\Jobs;

use App\Services\NlpApiKeys;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use RuntimeException;

/**
 * Revoke a person's keys, a team's keys, or a person's keys for a team, on
 * every NLP API endpoint. The API also refuses a key whose team its person is
 * no longer in, so a revocation that is late only matters until the next
 * user sync; it is still retried, and a 404 counts as a failure, since it
 * means an API that cannot manage keys yet.
 */
class RevokeApiKeysInNlpApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 50;

    public function __construct(public readonly ?string $email, public readonly ?string $teamId, $queue = 'high')
    {
        if ($email === null && $teamId === null) {
            throw new \InvalidArgumentException('Revoke by email, team or both');
        }
        $this->onQueue($queue);
        // Queued inside transactions: send it only once they commit.
        $this->afterCommit();
    }

    public function handle(): void
    {
        $endpoint = config('app.nlp_api_endpoint');
        if (empty($endpoint['sync_rules'])) {
            return;
        }

        $body = array_filter(['email' => $this->email, 'team_id' => $this->teamId], fn ($value) => $value !== null);
        foreach ((array) $endpoint['urls'] as $baseUrl) {
            if (empty($baseUrl)) {
                continue;
            }
            $url = rtrim($baseUrl, '/') . '/managed_keys/revoke';
            try {
                $response = NlpApiKeys::client()->post($url, $body);
            } catch (ConnectionException) {
                throw new RuntimeException("Unable to revoke keys at {$url}: unreachable");
            }
            if ($response->failed()) {
                throw new RuntimeException("Unable to revoke keys at {$url}: {$response->status()}");
            }
        }
    }
}
