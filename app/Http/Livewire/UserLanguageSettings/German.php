<?php

namespace App\Http\Livewire\UserLanguageSettings;

use App\Http\Livewire\UserGuidelineTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class German extends Component
{
    use AuthorizesRequests;
    use UserGuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $german_gender_ending;
    public $gendered_roles_format;
    public $gendered_roles_formats;

    protected $rules = [
        'german_gender_ending' => 'nullable|string|in::in,*in,/in,_in,In,/-in',
        'gendered_roles_format' => 'nullable|string|in:both,inclusive_gender,binary_gender,none',
    ];

    public $model;

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->enabled = false;

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $this->gendered_roles_formats = $languageGuidelines->getGenderedRolesFormats();
        if (empty($this->gendered_roles_formats)) {
            return;
        }

        $preferredVariants = $this->mountAttribute(
            $this->model,
            $languageGuidelines,
            'preferred_variants'
        );

        foreach ($preferredVariants as $variant) {
            if (strpos($variant, 'de') === 0) {
                $this->enabled = true;
            }
        }

        $this->german_gender_ending = $this->mountAttribute(
            $this->model,
            $languageGuidelines,
            'german_gender_ending',
            'german_rules'
        );

        $this->gendered_roles_format = $this->mountAttribute(
            $this->model,
            $languageGuidelines,
            'gendered_roles_format',
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
        $languageGuidelines->gendered_roles_format = $this->gendered_roles_format = $languageGuidelines->getGenderedRolesFormat($this->gendered_roles_format);

        $languageGuidelines->save();
        $languageGuidelines->dispatchEventToPosthog((new \ReflectionClass($this))->getShortName());

        $this->emit('saved');
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
