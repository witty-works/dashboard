<?php

namespace App\Http\Livewire\Teams;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AnalyticsUserAccess extends Component
{
    use AuthorizesRequests;

    public $user_access_to_team_analytics;

    protected $rules = [
        'user_access_to_team_analytics' => 'nullable|boolean',
    ];

    public $team;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->user_access_to_team_analytics = (bool) $team->user_access_to_team_analytics;
        if (!$this->team->subscribed()) {
            $this->user_access_to_team_analytics = true;
        }
    }

    public function updateUserAccessToTeamAnalytics()
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'update')) {
            abort(403);
        }

        $this->validate();

        if (!$this->team->subscribed()) {
            $this->user_access_to_team_analytics = true;
        }

        $this->team->user_access_to_team_analytics = (bool) $this->user_access_to_team_analytics;
        $this->team->save();

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.analytics-user-access');
    }
}
