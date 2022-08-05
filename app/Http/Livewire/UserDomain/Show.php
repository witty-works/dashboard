<?php

namespace App\Http\Livewire\UserDomain;

use App\Models\Domain;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

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
    public function mount($user)
    {
        $this->user = $user;
    }

    public function render()
    {
        $list = Domain::all()->where('user_id', $this->user->id)->sortByDesc('created_at');

        return view('livewire.user-domain.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
    }

    public function editDomain(Domain $domain)
    {
        $this->emit('edit', $domain->id);
    }

    public function deleteDomain(Domain $domain)
    {
        $domain->delete();
    }
}
