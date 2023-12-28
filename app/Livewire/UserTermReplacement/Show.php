<?php

namespace App\Livewire\UserTermReplacement;

use App\Livewire\HelpHeroTrait;
use App\Models\TermReplacement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    protected $listeners = ['saved'];

    public $model;

    public function mount($model)
    {
        $this->model = $model;
    }

    public function render()
    {
        $list = TermReplacement::all()->where('user_id', $this->model->id)->sortByDesc('created_at');

        return view('livewire.user-term-replacement.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
        $this->updateHelpHero();
    }

    public function editTermReplacement(TermReplacement $termReplacement)
    {
        if (Auth::user()->id !== $this->model->id) {
            abort(403);
        }

        $this->dispatch('edit', $termReplacement->id);
    }

    public function deleteTermReplacement(TermReplacement $termReplacement)
    {
        $termReplacement->delete();
        $this->updateHelpHero();
    }
}
