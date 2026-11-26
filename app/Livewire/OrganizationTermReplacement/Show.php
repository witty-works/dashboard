<?php

namespace App\Livewire\OrganizationTermReplacement;

use Livewire\Attributes\Locked;
use App\Livewire\GuidelineEntryAuthorization;
use App\Models\TermReplacement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use GuidelineEntryAuthorization;
    use AuthorizesRequests;

    protected $listeners = ['saved'];

    #[Locked]
    public $model;

    public $hide_actions;

    public function mount($model, $hide_actions = false)
    {
        $this->model = $model;
        $this->hide_actions = $hide_actions;
    }

    public function render()
    {
        if (!Auth::user()->hasTeamPermission($this->model, 'read')) {
            abort(403);
        }

        $list = TermReplacement::all()->where('team_id', $this->model->id)->sortByDesc('created_at');

        return view('livewire.organization-term-replacement.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
    }

    public function editTermReplacement(TermReplacement $termReplacement)
    {
        $this->authorizeTeamEntry($termReplacement);

        $this->dispatch('edit', termReplacement: $termReplacement);
    }

    public function deleteTermReplacement(TermReplacement $termReplacement)
    {
        $this->authorizeTeamEntry($termReplacement);

        $termReplacement->delete();
    }
}
