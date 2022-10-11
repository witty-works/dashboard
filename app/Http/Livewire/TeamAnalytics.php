<?php

namespace App\Http\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TeamAnalytics extends Component
{
    use AuthorizesRequests;

    public $team_analytics;

    protected $rules = [
        'team_analytics' => 'nullable|boolean',
    ];

    public $user;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($user)
    {
        $this->user = Auth::user();

        $this->team_analytics = (bool) $user->team_analytics;
        if (!$this->user->subscribed()) {
            $this->team_analytics = true;
        }
    }

    public function updateTeamsAnalytics()
    {
        $this->validate();

        if (!$this->user->subscribed()) {
            $this->team_analytics = true;
        }

        $this->user->team_analytics = (bool) $this->team_analytics;

        $this->user->save();

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.team-analytics');
    }
}
