<?php

namespace App\Livewire\OrganizationLanguageSettings;

use App\Livewire\TeamsGuidelineTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class French extends Component
{
    use AuthorizesRequests;
    use TeamsGuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $french_rules_force;
    public $french_gender_separator;

    protected $rules = [
        'french_rules_force' => 'nullable|boolean',
        'french_gender_separator' => 'nullable|string|in:·,·s,.,.s,/,/s',
    ];

    public $model;

    public static function isEnabled($genderedRolesFormat, $preferredVariants)
    {
        $enabled = false;

        if (in_array($genderedRolesFormat, ['inclusive_gender', 'both'])) {
            foreach ($preferredVariants as $variant) {
                if (strpos($variant, 'fr') === 0) {
                    $enabled = true;
                }
            }
        }

        return $enabled;
    }

    public function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $this->enabled = self::isEnabled(
            $languageGuidelines->gendered_roles_format,
            $languageGuidelines->preferred_variants
        );

        $this->french_rules_force = (bool) $languageGuidelines->french_rules_force;
        $this->french_gender_separator = $languageGuidelines->french_gender_separator;
    }

    public function updateLanguageGuidelinesFrench()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        if (!$this->model->isPremium()) {
            return;
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $languageGuidelines->french_rules_force = (bool) $this->french_rules_force;

        $languageGuidelines->french_gender_separator = $this->french_gender_separator;

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
        return view('livewire.organization-language-settings.french');
    }
}
