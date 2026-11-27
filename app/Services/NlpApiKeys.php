<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * The NLP API's keys for people and teams (its /managed_keys routes). The
 * API keeps the keys; the dashboard stores none. Creating, listing and
 * replacing go to the first configured endpoint, while a person waits;
 * revocations go to every endpoint, through RevokeApiKeysInNlpApi.
 */
class NlpApiKeys
{
    public function enabled(): bool
    {
        return $this->baseUrl() !== null && ! empty(config('app.nlp_api_endpoint.sync_rules'));
    }

    /** @return array{key: string, details: array} */
    public function create(User $user, Team $team, string $name, ?DateTimeInterface $expiresAt): array
    {
        return $this->send('post', '/managed_keys', [
            'email' => $user->email,
            'team_id' => $team->posthogId(),
            'name' => $name,
            'source' => 'dashboard',
            'expires_at' => $expiresAt?->format(DATE_ATOM),
        ])->json();
    }

    public function forUser(User $user): array
    {
        return $this->send('get', '/managed_keys?' . http_build_query(['email' => $user->email]))->json();
    }

    public function forTeam(Team $team): array
    {
        return $this->send('get', '/managed_keys?' . http_build_query(['team_id' => $team->posthogId()]))->json();
    }

    /** @return array{key: string, details: array} */
    public function replace(string $keyId, int $graceSeconds): array
    {
        return $this->send('post', '/managed_keys/' . rawurlencode($keyId) . '/replace', [
            'grace_seconds' => $graceSeconds,
        ])->json();
    }

    public function revoke(string $keyId): void
    {
        $this->send('delete', '/managed_keys/' . rawurlencode($keyId));
    }

    private function baseUrl(): ?string
    {
        foreach ((array) config('app.nlp_api_endpoint.urls') as $url) {
            if (! empty($url)) {
                return rtrim($url, '/');
            }
        }

        return null;
    }

    public static function client(): PendingRequest
    {
        $request = Http::timeout(10)->acceptJson();
        $user = config('app.nlp_api_endpoint.user');

        return empty($user) ? $request : $request->withBasicAuth($user, config('app.nlp_api_endpoint.password'));
    }

    private function send(string $method, string $path, ?array $body = null): Response
    {
        $base = $this->baseUrl();
        if ($base === null) {
            throw new NlpApiKeysUnavailable('No NLP API endpoint is configured');
        }

        try {
            $response = $body === null
                ? self::client()->{$method}($base . $path)
                : self::client()->{$method}($base . $path, $body);
        } catch (ConnectionException) {
            throw new NlpApiKeysUnavailable("The NLP API could not be reached for {$method} {$path}");
        }

        if ($response->failed()) {
            throw new NlpApiKeysUnavailable(
                "The NLP API answered {$response->status()} to {$method} " . strtok($path, '?'),
                $response->status(),
            );
        }

        return $response;
    }
}
