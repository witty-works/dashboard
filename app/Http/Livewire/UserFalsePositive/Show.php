<?php

namespace App\Http\Livewire\UserFalsePositive;

use App\Models\FalsePositive;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    protected $listeners = ['saved'];

    public $user;

    public function mount($user)
    {
        $this->user = $user;
    }

    public function render()
    {
        $list = FalsePositive::all()->where('user_id', $this->user->id)->sortByDesc('created_at');

        return view('livewire.user-false-positive.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
    }

    public function editFalsePositive(FalsePositive $falsePositive)
    {
        $this->emit('edit', $falsePositive->id);
    }

    public function deleteFalsePositive(FalsePositive $falsePositive)
    {
        $falsePositive->delete();
    }
}
