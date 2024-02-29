<?php

namespace App\Livewire;

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

    public $model;

    /**
     * Mount the component.
     *
     * @param  mixed  $model
     * @return void
     */
    public function mount($model)
    {
        $this->model = $model;

        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->team_analytics = (bool) $this->model->team_analytics;
        if (!$this->model->subscribed()) {
            $this->team_analytics = true;
        }
    }

    public function updateTeamsAnalytics()
    {
        $this->validate();

        if (Auth::user()->id !== $this->model->id) {
            abort(403);
        }

        if (!$this->model->subscribed()) {
            $this->team_analytics = true;
        }

        $this->model->team_analytics = (bool) $this->team_analytics;
        $this->model->save();

        $this->dispatch('saved');

        dispatch(new SyncUserToNlpApi($this->model, 'high'));
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
