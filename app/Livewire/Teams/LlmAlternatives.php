<?php

namespace App\Livewire\Teams;

use App\Livewire\HelpHeroTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LlmAlternatives extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    public $llm_alternatives;

    protected $rules = [
        'llm_alternatives' => 'nullable|boolean',
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

        $this->llm_alternatives = (bool) $this->model->llm_alternatives;
        if (!config('app.llm_enabled') || !$this->model->isPremium()) {
            $this->llm_alternatives = false;
        }
    }

    public function updateTeamsLlmAlternatives()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'update')) {
            abort(403);
        }

        // The toggle is hidden when the installation has LLM support switched
        // off, but the Livewire endpoint still accepts the update.
        if (!config('app.llm_enabled') || !$this->model->isPremium()) {
            $this->llm_alternatives = false;
        }

        $this->model->llm_alternatives = (bool) $this->llm_alternatives;

        $this->model->save();

        $this->dispatch('saved');
        $this->updateHelpHero();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.llm-alternatives');
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }
}
