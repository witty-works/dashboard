<?php

namespace App\Http\Livewire\OrganizationLanguageSettings;

use App\Http\Livewire\TeamsGuidelineTrait;
use App\Jobs\SyncOrganizationToNlpApi;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Orthography extends Component
{
    use AuthorizesRequests;
    use TeamsGuidelineTrait;

    public $disabled_categories;
    public $disabled_categories_force;
    public $disabled_categories_orthography;
    public $disabled_categories_orthography_force;

    protected $rules = [
        'disabled_categories_orthography' => 'nullable|boolean',
        'disabled_categories_orthography_force' => 'nullable|boolean',
    ];

    public $model;

    public function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $this->disabled_categories = (array) $languageGuidelines->disabled_categories;
        $this->disabled_categories_force = (array) $languageGuidelines->disabled_categories_force;

        $this->disabled_categories_orthography = !in_array('orthography', $this->disabled_categories);
        $this->disabled_categories_orthography_force = in_array('orthography', $this->disabled_categories_force);
    }

    public function updateLanguageGuidelinesOrthography()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);
        $languageGuidelines->save();

        $languageGuidelines->inPlaceUpateArray('orthography', 'disabled_categories', $this->disabled_categories_orthography);

        $disabled_categories = [];
        if (!$this->disabled_categories_orthography) {
            $disabled_categories[] = 'orthography';
        }

        $this->disabled_categories = $disabled_categories;

        if (!$this->model->subscribed()) {
            $this->disabled_categories_orthography_force = false;
        }
        $languageGuidelines->inPlaceUpateArray('orthography', 'disabled_categories_force', !$this->disabled_categories_orthography_force);

        $languageGuidelines->save();

        $languageGuidelines->dispatchEventToPosthog((new \ReflectionClass($this))->getShortName());

        $this->emit('saved');
        $this->updateHelpHero();

        dispatch(new SyncOrganizationToNlpApi($this->model, 'high'));
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
