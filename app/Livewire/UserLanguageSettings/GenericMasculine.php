<?php

namespace App\Livewire\UserLanguageSettings;

use App\Livewire\OrganizationLanguageSettings\GenericMasculine as OrganizationLanguageSettingsGenericMasculine;
use App\Livewire\UserGuidelineTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GenericMasculine extends Component
{
    use AuthorizesRequests;
    use UserGuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $gendered_roles_format;

    protected $rules = [
        'gendered_roles_format' => 'nullable|string|in:both,inclusive_gender,binary_gender,none',
    ];

    public $model;

    protected function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $preferredVariants = $this->mountAttribute(
            $this->model,
            $languageGuidelines,
            'preferred_variants'
        );

        $this->enabled = OrganizationLanguageSettingsGenericMasculine::isEnabled($preferredVariants);

        $this->gendered_roles_format = $this->mountAttribute(
            $this->model,
            $languageGuidelines,
            'gendered_roles_format',
            'generic_masculine'
        );
    }

    public function updateLanguageGuidelinesGenericMasculine()
    {
        $this->validate();

        if (Auth::user()->id !== $this->model->id) {
            abort(403);
        }

        if (!$this->model->isPremium()) {
            return;
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

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
        return view('livewire.user-language-settings.generic_masculine');
    }
}
