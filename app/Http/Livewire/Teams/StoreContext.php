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

    public $model;

    /**
     * Mount the component.
     *
     * @param  mixed  $model
     * @return void
     */
    public function mount($model)
    {
        $this->model = $model;

        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->store_context = (bool) $this->model->store_context;
        if (!$this->model->subscribed()) {
            $this->store_context = true;
        }
    }

    public function updateTeamsStoreContext()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'update')) {
            abort(403);
        }

        if (!$this->model->subscribed()) {
            $this->store_context = true;
        }

        $this->model->store_context = (bool) $this->store_context;

        $this->model->save();

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
