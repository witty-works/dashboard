<?php

namespace App\Http\Livewire\FalsePositive;

use App\Models\FalsePositive;
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

        $list = FalsePositive::all()->where('team_id', $this->team->id)->sortByDesc('created_at');

        return view('livewire.false-positive.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
    }

    public function deleteFalsePositive(FalsePositive $falsePositive)
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $falsePositive->delete();
    }
}
