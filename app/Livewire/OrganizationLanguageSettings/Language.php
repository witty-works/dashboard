<?php

namespace App\Livewire\OrganizationLanguageSettings;

use App\Livewire\TeamsGuidelineTrait;
use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Language extends Component
{
    use AuthorizesRequests;
    use TeamsGuidelineTrait;

    public $preferred_variants;
    public $preferred_variants_force;
    public $preferred_variants_de;
    public $preferred_variants_en;
    public $preferred_variants_fr;

    protected $rules = [
        'preferred_variants_force' => 'nullable|boolean',
        'preferred_variants_de' => 'nullable|string|in:de-DE,de-AT,de-CH',
        'preferred_variants_en' => 'nullable|string|in:en-US,en-GB',
        'preferred_variants_fr' => 'nullable|string|in:fr-FR,fr-CH',
    ];

    public $model;

    public function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $this->preferred_variants = (array) $languageGuidelines->preferred_variants;
        $this->preferred_variants_force = (bool) $languageGuidelines->preferred_variants_force;

        foreach (GuidelinesInterface::LANGUAGES as $lang) {
            $property = "preferred_variants_$lang";
            foreach (constant('App\Models\GuidelinesInterface::PREFERRED_VARIANTS_' . strtoupper($lang)) as $locale => $trans_key) {
                if (in_array($locale, $this->preferred_variants)) {
                    $this->$property = $locale;
                }
            }
        }
    }

    public function updateLanguageGuidelinesLanguage()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $preferredVariants = [];
        $oneEnabled = false;
        foreach (GuidelinesInterface::LANGUAGES as $lang) {
            $property = "preferred_variants_$lang";
            if ($this->$property) {
                $preferredVariants[] = $this->$property;
                $oneEnabled = true;
            }
        }

        if (!$oneEnabled) {
            $message = __('guidelines.enable_at_least_one_variant');
            throw ValidationException::withMessages(['preferred_variants' => $message]);
        }

        $this->preferred_variants = $preferredVariants;

        $languageGuidelines->preferred_variants = $this->preferred_variants;

        if ($this->model->isPremium()) {
            $languageGuidelines->preferred_variants_force = (bool) $this->preferred_variants_force;
        }

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
        return view('livewire.organization-language-settings.language');
    }
}
