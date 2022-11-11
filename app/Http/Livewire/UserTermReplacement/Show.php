<?php

namespace App\Http\Livewire\UserTermReplacement;

use App\Http\Livewire\HelpHeroTrait;
use App\Models\TermReplacement;
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
        $list = TermReplacement::all()->where('user_id', $this->user->id)->sortByDesc('created_at');

        return view('livewire.user-term-replacement.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
        $this->updateHelpHero();
    }

    public function editTermReplacement(TermReplacement $termReplacement)
    {
        $this->emit('edit', $termReplacement->id);
    }

    public function deleteTermReplacement(TermReplacement $termReplacement)
    {
        $termReplacement->delete();
        $this->updateHelpHero();
    }
}
