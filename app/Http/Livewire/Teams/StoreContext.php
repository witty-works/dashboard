<?php

namespace App\Http\Livewire\Teams;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class StoreContext extends Component
{
    use AuthorizesRequests;

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

        $this->store_context = (bool) $team->store_context;
        if ($this->team->getCanStoreContextBeDisabled()) {
            $this->store_context = true;
        }
    }

    public function updateTeamsStoreContext()
    {
        $this->validate();

        if (!$this->team->getCanStoreContextBeDisabled()) {
            abort(403);
        }

        $this->team->store_context = (bool) $this->store_context;

        $this->team->save();

        $this->emit('saved');
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
}
