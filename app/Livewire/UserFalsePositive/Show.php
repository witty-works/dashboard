<?php

namespace App\Livewire\UserFalsePositive;

use App\Livewire\HelpHeroTrait;
use App\Models\FalsePositive;
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
        $list = FalsePositive::all()->where('user_id', $this->model->id)->sortByDesc('created_at');

        return view('livewire.user-false-positive.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
        $this->updateHelpHero();
    }

    public function editFalsePositive(FalsePositive $falsePositive)
    {
        if (Auth::user()->id !== $this->model->id) {
            abort(403);
        }

        $this->dispatch('edit', $falsePositive->id);
    }

    public function deleteFalsePositive(FalsePositive $falsePositive)
    {
        $falsePositive->delete();
        $this->updateHelpHero();
    }
}
