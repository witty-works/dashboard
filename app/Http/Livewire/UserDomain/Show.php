<?php

namespace App\Http\Livewire\UserDomain;

use App\Http\Livewire\HelpHeroTrait;
use App\Models\Domain;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    protected $listeners = ['saved'];

    /**
     * The user instance.
     *
     * @var mixed
     */
    public $user;

    /**
     * Mount the component.
     *
     * @param  mixed  $userGuidelines
     * @return void
     */
    public function mount()
    {
        $this->user = Auth::user();
    }

    public function render()
    {
        $list = Domain::all()->where('user_id', $this->user->id)->sortByDesc('created_at');

        return view('livewire.user-domain.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
        $this->updateHelpHero();
    }

    public function editDomain(Domain $domain)
    {
        $this->emit('edit', $domain->id);
    }

    public function deleteDomain(Domain $domain)
    {
        $domain->delete();
        $this->updateHelpHero();
    }
}
