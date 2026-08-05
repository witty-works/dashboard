<?php

namespace App\Livewire\Teams;

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

        $this->user_access_to_team_analytics = (bool) $this->model->user_access_to_team_analytics;
    }

    public function updateUserAccessToTeamAnalytics()
    {
        if (!Auth::user()->hasTeamPermission($this->model, 'update')) {
            abort(403);
        }

        $this->validate();

        $this->model->user_access_to_team_analytics = (bool) $this->user_access_to_team_analytics;
        $this->model->save();

        $this->dispatch('saved');
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

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }
}
