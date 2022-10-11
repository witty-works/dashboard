<?php

namespace App\Http\Livewire\UserTermReplacement;

use App\Models\TermReplacement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

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
    }

    public function editTermReplacement(TermReplacement $termReplacement)
    {
        $this->emit('edit', $termReplacement->id);
    }

    public function deleteTermReplacement(TermReplacement $termReplacement)
    {
        $termReplacement->delete();
    }
}
