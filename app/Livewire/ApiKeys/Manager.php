<?php

namespace App\Livewire\ApiKeys;

use App\Models\Team;
use App\Services\NlpApiKeys;
use App\Services\NlpApiKeysUnavailable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * A person's API keys, kept by the NLP API. A new key reaches the browser
 * once, in a browser event, and never in the component's state, which every
 * later request sends back.
 */
class Manager extends Component
{
    public const GRACE_PERIODS = [0, 86400, 604800];

    public string $name = '';

    public ?int $teamId = null;

    public ?string $expiresOn = null;

    /** Grace period per key id, for "Replace". */
    public array $grace = [];

    public function mount(): void
    {
        $this->teamId = $this->teams()->count() === 1 ? $this->teams()->first()->id : null;
    }

    /** The teams of the signed-in person, by id. */
    private function teams(): Collection
    {
        return auth()->user()->allTeams()->keyBy('id');
    }

    /** The person's keys, by id, as the API lists them. */
    private function ownKeys(NlpApiKeys $keys): Collection
    {
        return collect($keys->forUser(auth()->user()))->keyBy('id');
    }

    /**
     * The route needs a verified address, but Livewire does not re-run that
     * middleware on actions: an address changed in another tab would get keys
     * under an address its person does not own yet.
     */
    private function ensureVerified(): void
    {
        abort_unless(auth()->user()->hasVerifiedEmail(), 403);
    }

    public function create(NlpApiKeys $keys): void
    {
        $this->ensureVerified();
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'teamId' => ['required', 'integer', 'in:' . $this->teams()->keys()->implode(',')],
            'expiresOn' => ['nullable', 'date_format:Y-m-d', 'after:today'],
        ]);

        $team = $this->teams()->get($this->teamId);
        $expiresAt = $this->expiresOn ? Carbon::createFromFormat('Y-m-d', $this->expiresOn, 'UTC')->endOfDay() : null;

        try {
            $created = $keys->create(auth()->user(), $team, $this->name, $expiresAt);
        } catch (NlpApiKeysUnavailable $error) {
            $this->addError('name', $error->status === 409 ? __('content.api_keys_too_many') : __('content.api_keys_unavailable'));

            return;
        }

        $this->dispatch('api-key-created', key: $created['key']);
        $this->reset('name', 'expiresOn');
    }

    public function replace(string $keyId, NlpApiKeys $keys): void
    {
        $this->ensureVerified();
        $grace = (int) ($this->grace[$keyId] ?? 86400);
        abort_unless(in_array($grace, self::GRACE_PERIODS, true), 422);

        try {
            $key = $this->ownKeys($keys)->get($keyId);
            $teamIds = $this->teams()->map(fn (Team $team) => $team->posthogId());
            abort_if($key === null || ($key['team_id'] !== null && ! $teamIds->contains($key['team_id'])), 403);

            $replaced = $keys->replace($keyId, $grace);
        } catch (NlpApiKeysUnavailable) {
            $this->addError('keys', __('content.api_keys_unavailable'));

            return;
        }

        $this->dispatch('api-key-created', key: $replaced['key']);
    }

    public function revoke(string $keyId, NlpApiKeys $keys): void
    {
        $this->ensureVerified();

        try {
            abort_unless($this->ownKeys($keys)->has($keyId), 403);
            $keys->revoke($keyId);
        } catch (NlpApiKeysUnavailable) {
            $this->addError('keys', __('content.api_keys_unavailable'));
        }
    }

    public function render(NlpApiKeys $keys)
    {
        $teams = $this->teams();
        $teamNames = $teams->mapWithKeys(fn (Team $team) => [$team->posthogId() => $team->name]);

        try {
            $list = $this->ownKeys($keys)->values();
            $unavailable = false;
        } catch (NlpApiKeysUnavailable) {
            $list = collect();
            $unavailable = true;
        }

        return view('livewire.api-keys.manager', [
            'teams' => $teams,
            'keys' => $list,
            'teamNames' => $teamNames,
            'unavailable' => $unavailable,
        ]);
    }
}
