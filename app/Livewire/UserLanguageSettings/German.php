<?php

namespace App\Livewire\UserLanguageSettings;

use App\Livewire\OrganizationLanguageSettings\German as OrganizationLanguageSettingsGerman;
use App\Livewire\UserGuidelineTrait;
use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class German extends Component
{
    use AuthorizesRequests;
    use UserGuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $german_gender_ending;

    protected function rules()
    {
        return [
            'german_gender_ending' => [
                'nullable',
                'string',
                Rule::in(array_keys(GuidelinesInterface::GERMAN_GENDER_ENDING)),
            ],
        ];
    }

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

        $this->enabled = OrganizationLanguageSettingsGerman::isEnabled(
            $genderedRolesFormat,
            $preferredVariants
        );

        $this->german_gender_ending = $this->mountAttribute(
            $this->model,
            $languageGuidelines,
            'german_gender_ending',
            'german_rules'
        );
    }

    public function updateLanguageGuidelinesGerman()
    {
        $this->validate();

        if (Auth::user()->id !== $this->model->id) {
            abort(403);
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $languageGuidelines->german_gender_ending = $this->german_gender_ending;

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
        return view('livewire.user-language-settings.german');
    }
}
