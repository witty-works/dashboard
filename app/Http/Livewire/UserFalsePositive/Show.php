<?php

namespace App\Http\Livewire\UserFalsePositive;

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

    public $user;

    public function mount()
    {
        $this->user = Auth::user();
    }

    public function render()
    {
        $list = FalsePositive::all()->where('user_id', $this->user->id)->sortByDesc('created_at');

        return view('livewire.user-false-positive.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
        $this->updateHelpHero();
    }

    public function editFalsePositive(FalsePositive $falsePositive)
    {
        $this->emit('edit', $falsePositive->id);
    }

    public function deleteFalsePositive(FalsePositive $falsePositive)
    {
        $falsePositive->delete();
        $this->updateHelpHero();
    }
}
