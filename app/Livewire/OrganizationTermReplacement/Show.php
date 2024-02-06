<?php

namespace App\Livewire\OrganizationTermReplacement;

use App\Livewire\HelpHeroTrait;
use App\Models\TermReplacement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    protected $listeners = ['saved'];

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
        $this->updateHelpHero();
    }

    public function editTermReplacement(TermReplacement $termReplacement)
    {
        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $this->dispatch('edit', termReplacement: $termReplacement);
    }

    public function deleteTermReplacement(TermReplacement $termReplacement)
    {
        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $termReplacement->delete();
        $this->updateHelpHero();
    }
}
