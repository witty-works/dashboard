<?php

namespace App\Livewire\OrganizationLanguageSettings;

use App\Livewire\TeamsGuidelineTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Inspirations extends Component
{
    use AuthorizesRequests;
    use TeamsGuidelineTrait;

    public $show_inspiration_alternatives;
    public $show_inspiration_alternatives_force;

    protected $rules = [
        'show_inspiration_alternatives' => 'nullable|boolean',
        'show_inspiration_alternatives_force' => 'nullable|boolean',
    ];

    public $model;

    public function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $this->show_inspiration_alternatives = (bool) $languageGuidelines->show_inspiration_alternatives;
        $this->show_inspiration_alternatives_force = (bool) $languageGuidelines->show_inspiration_alternatives_force;
    }

    public function updateLanguageGuidelinesInspirations()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $languageGuidelines->show_inspiration_alternatives = (bool) $this->show_inspiration_alternatives;
        $languageGuidelines->show_inspiration_alternatives_force = (bool) $this->show_inspiration_alternatives_force;
        $languageGuidelines->save();
        $languageGuidelines->dispatchEventToPosthog((new \ReflectionClass($this))->getShortName());

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
        return view('livewire.organization-language-settings.inspirations');
    }
}
