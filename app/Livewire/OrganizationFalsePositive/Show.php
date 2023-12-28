<?php

namespace App\Livewire\OrganizationFalsePositive;

use App\Livewire\HelpHeroTrait;
use App\Models\FalsePositive;
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

    public function mount($model, $hide_actions = False)
    {
        $this->model = $model;
        $this->hide_actions = $hide_actions;
    }

    public function render()
    {
        if (!Auth::user()->hasTeamPermission($this->model, 'read')) {
            abort(403);
        }

        $list = FalsePositive::all()->where('team_id', $this->model->id)->sortByDesc('created_at');

        return view('livewire.organization-false-positive.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
        $this->updateHelpHero();
    }

    public function editFalsePositive(FalsePositive $falsePositive)
    {
        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $this->dispatch('edit', $falsePositive->id);
    }

    public function deleteFalsePositive(FalsePositive $falsePositive)
    {
        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $falsePositive->delete();
        $this->updateHelpHero();
    }
}
