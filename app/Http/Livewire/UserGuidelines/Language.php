<?php

namespace App\Http\Livewire\UserGuidelines;

use App\Models\GuidelinesInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Language extends Component
{
    use AuthorizesRequests;
    use GuidelineTrait;

    public $preferred_variants;
    public $preferred_variants_de;
    public $preferred_variants_en;

    protected $rules = [
        'preferred_variants_de' => 'nullable|string|in:de-DE,de-AT,de-CH',
        'preferred_variants_en' => 'nullable|string|in:en-US,en-GB',
    ];

    public $user;

    protected function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $this->preferred_variants = (array) $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'preferred_variants'
        );

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

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

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
        return view('livewire.user-guidelines.language');
    }
}
