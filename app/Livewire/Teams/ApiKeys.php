<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Services\NlpApiKeys;
use App\Services\NlpApiKeysUnavailable;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The keys members created for a team, for its owner and admins: who, which
 * name, when; never the key. They can revoke any of them.
 */
class ApiKeys extends Component
{
    #[Locked]
    public Team $team;

    public function mount(Team $team): void
    {
        Gate::authorize('updateTeamMember', $team);
        $this->team = $team;
    }

    public function revoke(string $keyId, NlpApiKeys $keys): void
    {
        Gate::authorize('updateTeamMember', $this->team);
        abort_unless(collect($keys->forTeam($this->team))->contains('id', $keyId), 403);

        try {
            $keys->revoke($keyId);
        } catch (NlpApiKeysUnavailable) {
            $this->addError('keys', __('content.api_keys_unavailable'));
        }
    }

    public function render(NlpApiKeys $keys)
    {
        try {
            $list = collect($keys->forTeam($this->team));
            $unavailable = false;
        } catch (NlpApiKeysUnavailable) {
            $list = collect();
            $unavailable = true;
        }

        return view('livewire.teams.api-keys', ['keys' => $list, 'unavailable' => $unavailable]);
    }
}
