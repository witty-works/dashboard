<?php

namespace App\Http\Livewire\OrganizationTermReplacement;

use App\Models\TermReplacement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

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

        $list = TermReplacement::all()->where('team_id', $this->team->id)->sortByDesc('created_at');

        return view('livewire.organization-term-replacement.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
    }

    public function editTermReplacement(TermReplacement $termReplacement)
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $this->emit('edit', $termReplacement->id);
    }

    public function deleteTermReplacement(TermReplacement $termReplacement)
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $termReplacement->delete();
    }
}
