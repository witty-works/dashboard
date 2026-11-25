<?php

namespace App\Livewire\UserDomain;

use Livewire\Attributes\Locked;
use App\Livewire\GuidelineEntryAuthorization;
use App\Livewire\HelpHeroTrait;
use App\Models\Domain;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use GuidelineEntryAuthorization;
    use AuthorizesRequests;
    use HelpHeroTrait;

    protected $listeners = ['saved'];

    /**
     * The user instance.
     *
     * @var mixed
     */
    #[Locked]
    public $model;

    /**
     * Mount the component.
     *
     * @param  mixed  $userGuidelines
     * @return void
     */
    public function mount($model)
    {
        $this->model = $model;
    }

    public function render()
    {
        $list = Domain::all()->where('user_id', $this->model->id)->sortByDesc('created_at');

        return view('livewire.user-domain.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
        $this->updateHelpHero();
    }

    public function editDomain(Domain $domain)
    {
        $this->authorizeUserEntry($domain);

        $this->dispatch('edit', domain: $domain);
    }

    public function deleteDomain(Domain $domain)
    {
        $this->authorizeUserEntry($domain);

        $domain->delete();
        $this->updateHelpHero();
    }
}
