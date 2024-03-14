<?php

namespace App\Livewire\OrganizationLanguageSettings;

use App\Livewire\TeamsGuidelineTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Orthography extends Component
{
    use AuthorizesRequests;
    use TeamsGuidelineTrait;

    public $orthography;
    public $orthography_force;

    protected $rules = [
        'orthography' => 'nullable|boolean',
        'orthography_force' => 'nullable|boolean',
    ];

    public $model;

    public function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $this->orthography = !in_array('orthography', $languageGuidelines->disabled_categories);
        $this->orthography_force = in_array('orthography', $languageGuidelines->disabled_categories_force);
    }

    public function updateLanguageGuidelinesOrthography()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        if ($this->model->subscribed()) {
            $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

            $languageGuidelines->inPlaceUpateArray('orthography', 'disabled_categories', $this->orthography);
            $languageGuidelines->inPlaceUpateArray('orthography', 'disabled_categories_force', !$this->orthography_force);

            $languageGuidelines->save();
            $languageGuidelines->dispatchEventToPosthog((new \ReflectionClass($this))->getShortName());
        }

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
        return view('livewire.organization-language-settings.orthography');
    }
}
