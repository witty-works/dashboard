<?php

namespace App\Http\Livewire;

use App\Jobs\SyncUserToNlpApi;
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

        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->team_analytics = (bool) $this->user->team_analytics;
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

        dispatch(new SyncUserToNlpApi($this->user, 'high'));
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

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }
}
