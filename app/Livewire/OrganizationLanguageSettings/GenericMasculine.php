<?php

namespace App\Livewire\OrganizationLanguageSettings;

use App\Livewire\TeamsGuidelineTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GenericMasculine extends Component
{
    use AuthorizesRequests;
    use TeamsGuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $generic_masculine_force;
    public $gendered_roles_format;

    protected $rules = [
        'generic_masculine_force' => 'nullable|boolean',
        'gendered_roles_format' => 'nullable|string|in:both,inclusive_gender,binary_gender,none',
    ];

    public $model;

    public static function isEnabled($preferredVariants)
    {
        $enabled = false;

        foreach ($preferredVariants as $variant) {
            if (strpos($variant, 'de') === 0 || strpos($variant, 'fr') === 0) {
                $enabled = true;
            }
        }

        return $enabled;
    }

    public function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $this->enabled = self::isEnabled($languageGuidelines->preferred_variants);

        $this->generic_masculine_force = (bool) $languageGuidelines->generic_masculine_force;
        $this->gendered_roles_format = $languageGuidelines->getGenderedRolesFormat();
    }

    public function updateLanguageGuidelinesGenericMasculine()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        if (!$this->model->isPremium()) {
            return;
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $languageGuidelines->generic_masculine_force = (bool) $this->generic_masculine_force;

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
        return view('livewire.organization-language-settings.generic_masculine');
    }
}
