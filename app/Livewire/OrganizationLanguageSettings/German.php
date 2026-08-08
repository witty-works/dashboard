<?php

namespace App\Livewire\OrganizationLanguageSettings;

use App\Livewire\TeamsGuidelineTrait;
use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class German extends Component
{
    use AuthorizesRequests;
    use TeamsGuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $german_rules_force;
    public $german_gender_ending;

    protected function rules()
    {
        return [
            'german_rules_force' => 'nullable|boolean',
            'german_gender_ending' => [
                'nullable',
                'string',
                Rule::in(array_keys(GuidelinesInterface::GERMAN_GENDER_ENDING)),
            ],
        ];
    }

    public $model;

    public static function isEnabled($genderedRolesFormat, $preferredVariants)
    {
        $enabled = false;

        if (in_array($genderedRolesFormat, ['inclusive_gender', 'both'])) {
            foreach ($preferredVariants as $variant) {
                if (strpos($variant, 'de') === 0) {
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

        $this->german_rules_force = (bool) $languageGuidelines->german_rules_force;
        $this->german_gender_ending = $languageGuidelines->german_gender_ending;
    }

    public function updateLanguageGuidelinesGerman()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $languageGuidelines->german_rules_force = (bool) $this->german_rules_force;

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
        return view('livewire.organization-language-settings.german');
    }
}
