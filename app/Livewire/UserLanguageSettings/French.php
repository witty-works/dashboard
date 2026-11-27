<?php

namespace App\Livewire\UserLanguageSettings;

use Livewire\Attributes\Locked;
use App\Livewire\OrganizationLanguageSettings\French as OrganizationLanguageSettingsFrench;
use App\Livewire\UserGuidelineTrait;
use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class French extends Component
{
    use AuthorizesRequests;
    use UserGuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $french_gender_separator;
    public $french_gender_suffix;

    protected function rules(): array
    {
        return [
            'french_gender_separator' => ['nullable', 'string', Rule::in(array_keys(GuidelinesInterface::FRENCH_GENDER_SEPARATOR))],
            'french_gender_suffix' => ['required', 'string', Rule::in(array_keys(GuidelinesInterface::FRENCH_GENDER_SUFFIX))],
        ];
    }

    #[Locked]
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

        $this->french_gender_suffix = $this->mountAttribute(
            $this->model,
            $languageGuidelines,
            'french_gender_suffix',
            'french_rules'
        ) ?? 'short';
    }

    public function updateLanguageGuidelinesFrench()
    {
        $this->validate();

        if (Auth::user()->id !== $this->model->id) {
            abort(403);
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $languageGuidelines->french_gender_separator = $this->french_gender_separator;
        $languageGuidelines->french_gender_suffix = $this->french_gender_suffix;

        $languageGuidelines->save();
        $languageGuidelines->dispatchEventToPosthog((new \ReflectionClass($this))->getShortName());

        $this->dispatch('saved');
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
