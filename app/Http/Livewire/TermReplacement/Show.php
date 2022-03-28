<?php

namespace App\Http\Livewire\TermReplacement;

use App\Models\TermReplacement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    protected $listeners = ['saved'];

    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * Mount the component.
     *
     * @param  mixed  $organizationGuidelines
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;
    }

    public function render()
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'read')) {
            abort(403);
        }

        $list = TermReplacement::all()->where('team_id', $this->team->id)->sortByDesc('created_at');

        return view('livewire.term-replacement.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
    }

    public function deleteTermReplacement(TermReplacement $termReplacement)
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $termReplacement->delete();
    }
}
