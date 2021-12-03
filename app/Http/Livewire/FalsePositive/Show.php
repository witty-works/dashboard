<?php

namespace App\Http\Livewire\FalsePositive;

use App\Models\FalsePositive;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    protected $listeners = ['saved'];

    public function render()
    {
        $list = FalsePositive::all()->sortByDesc('created_at');

        return view('livewire.false-positive.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
    }

    public function deleteFalsePositive(FalsePositive $falsePositive)
    {
        $user = auth()->user();
        if ($user && $user->currentTeam) {
            $this->authorize('update', $user->currentTeam);

            $falsePositive->delete();
        }
    }
}
