<?php

namespace App\Livewire\UserLanguageSettings;

use App\Livewire\OrganizationLanguageSettings\French as OrganizationLanguageSettingsFrench;
use App\Livewire\UserGuidelineTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class French extends Component
{
    use AuthorizesRequests;
    use UserGuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $french_gender_separator;

    protected $rules = [
        'french_gender_separator' => 'nullable|string|in:·,·s,.,.s,/,/s',
    ];

    public $model;

    protected function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $genderedRolesFormat = $this->mountAttribute(
            $this->model,
            $languageGuidelines,
            'gendered_roles_format',
            'generic_masculine'
        );

        $preferredVariants = $this->mountAttribute(
            $this->model,
            $languageGuidelines,
            'preferred_variants'
        );

        $this->enabled = OrganizationLanguageSettingsFrench::isEnabled(
            $genderedRolesFormat,
            $preferredVariants
        );

        $this->french_gender_separator = $this->mountAttribute(
            $this->model,
            $languageGuidelines,
            'french_gender_separator',
            'french_rules'
        );
    }

    public function updateLanguageGuidelinesFrench()
    {
        $this->validate();

        if (Auth::user()->id !== $this->model->id) {
            abort(403);
        }

        if (!$this->model->isPremium()) {
            return;
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

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
        return view('livewire.user-language-settings.french');
    }
}
