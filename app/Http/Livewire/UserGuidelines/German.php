<?php

namespace App\Http\Livewire\UserGuidelines;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class German extends Component
{
    use AuthorizesRequests;
    use GuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $german_gender_ending;
    public $gendered_roles_format;

    protected $rules = [
        'german_gender_ending' => 'nullable|string|in::in,*in,/in,_in,In,/-in',
        'gendered_roles_format' => 'nullable|string|in:both,inclusive_gender,binary_gender,none',
    ];

    public $user;

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->enabled = false;

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $preferred_variants = $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'preferred_variants'
        );
        foreach ($preferred_variants as $variant) {
            if (strpos($variant, 'de') === 0) {
                $this->enabled = true;
            }
        }

        $this->german_gender_ending = $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'german_gender_ending',
            'german_rules'
        );

        $this->gendered_roles_format = $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'gendered_roles_format',
            'german_rules'
        );
    }

    public function updateLanguageGuidelinesGerman()
    {
        $this->validate();

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $languageGuidelines->german_gender_ending = $this->german_gender_ending;
        
        if ($this->user->subscribed()) {
            $languageGuidelines->gendered_roles_format = $this->gendered_roles_format;
        }

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
        return view('livewire.user-guidelines.german');
    }
}
