<?php

namespace App\Http\Livewire\Teams;

use App\Http\Livewire\HelpHeroTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StoreContext extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    public $store_context;

    protected $rules = [
        'store_context' => 'nullable|boolean',
    ];

    public $team;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->store_context = (bool) $this->team->store_context;
        if (!$this->team->subscribed()) {
            $this->store_context = true;
        }
    }

    public function updateTeamsStoreContext()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'update')) {
            abort(403);
        }

        if (!$this->team->subscribed()) {
            $this->store_context = true;
        }

        $this->team->store_context = (bool) $this->store_context;

        $this->team->save();

        $this->emit('saved');
        $this->updateHelpHero();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.store-context');
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }
}
