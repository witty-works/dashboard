<?php

namespace App\Livewire\UserTermReplacement;

use Livewire\Attributes\Locked;
use App\Livewire\GuidelineEntryAuthorization;
use App\Livewire\HelpHeroTrait;
use App\Models\TermReplacement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use GuidelineEntryAuthorization;
    use AuthorizesRequests;
    use HelpHeroTrait;

    protected $listeners = ['saved'];

    #[Locked]
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
        $this->authorizeUserEntry($termReplacement);

        $this->dispatch('edit', termReplacement: $termReplacement);
    }

    public function deleteTermReplacement(TermReplacement $termReplacement)
    {
        $this->authorizeUserEntry($termReplacement);

        $termReplacement->delete();
        $this->updateHelpHero();
    }
}
