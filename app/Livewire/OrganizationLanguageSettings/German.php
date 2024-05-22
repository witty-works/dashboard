<?php

namespace App\Livewire\OrganizationLanguageSettings;

use App\Livewire\TeamsGuidelineTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class German extends Component
{
    use AuthorizesRequests;
    use TeamsGuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $german_rules_force;
    public $german_gender_ending;
    public $gendered_roles_format;

    protected $rules = [
        'german_rules_force' => 'nullable|boolean',
        'german_gender_ending' => 'nullable|string|in::in,*in,/in,_in,In,/-in',
        'gendered_roles_format' => 'nullable|string|in:both,inclusive_gender,binary_gender,none',
    ];

    public $model;

    public function resetForm()
    {
        $this->resetErrorBag();

        $this->enabled = false;

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        foreach ($languageGuidelines->preferred_variants as $variant) {
            if (strpos($variant, 'de') === 0) {
                $this->enabled = true;
            }
        }

        $this->german_rules_force = (bool) $languageGuidelines->german_rules_force;
        $this->german_gender_ending = $languageGuidelines->german_gender_ending;
        $this->gendered_roles_format = $languageGuidelines->getGenderedRolesFormat();
    }

    public function updateLanguageGuidelinesGerman()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        if (!$this->model->isPremium()) {
            return;
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $languageGuidelines->german_rules_force = (bool) $this->german_rules_force;

        $languageGuidelines->german_gender_ending = $this->german_gender_ending;

        $languageGuidelines->gendered_roles_format = $this->gendered_roles_format = $languageGuidelines->getGenderedRolesFormat($this->gendered_roles_format);

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
        return view('livewire.organization-language-settings.german');
    }
}
