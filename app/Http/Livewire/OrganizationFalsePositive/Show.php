<?php

namespace App\Http\Livewire\OrganizationFalsePositive;

use App\Http\Livewire\HelpHeroTrait;
use App\Models\FalsePositive;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    protected $listeners = ['saved'];

    public $team;

    public $hide_actions;

    public function mount($team, $hide_actions = False)
    {
        $this->team = $team;
        $this->hide_actions = $hide_actions;
    }

    public function render()
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'read')) {
            abort(403);
        }

        $list = FalsePositive::all()->where('team_id', $this->team->id)->sortByDesc('created_at');

        return view('livewire.organization-false-positive.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
        $this->updateHelpHero();
    }

    public function editFalsePositive(FalsePositive $falsePositive)
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $this->emit('edit', $falsePositive->id);
    }

    public function deleteFalsePositive(FalsePositive $falsePositive)
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $falsePositive->delete();
        $this->updateHelpHero();
    }
}
