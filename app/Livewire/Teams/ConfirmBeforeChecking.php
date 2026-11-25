<?php

namespace App\Livewire\Teams;

use Livewire\Attributes\Locked;
use App\Livewire\HelpHeroTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * "Ask before checking each field": when on, every member's browser extension
 * asks before it sends a text field's content to the NLP API. Synced as a forced
 * setting, so members cannot switch it off; see SyncOrganizationToNlpApi.
 */
class ConfirmBeforeChecking extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    public $confirm_before_checking;

    protected $rules = [
        'confirm_before_checking' => 'nullable|boolean',
    ];

    #[Locked]
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

        $this->confirm_before_checking = (bool) $this->model->confirm_before_checking;
    }

    public function updateTeamsConfirmBeforeChecking()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'update')) {
            abort(403);
        }

        $this->model->confirm_before_checking = (bool) $this->confirm_before_checking;

        $this->model->save();

        $this->dispatch('saved');
        $this->updateHelpHero();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.confirm-before-checking');
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }
}
