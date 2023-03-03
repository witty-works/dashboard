<?php

namespace App\Http\Livewire\UserGuidelines;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class English extends Component
{
    use AuthorizesRequests;
    use GuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $singular_they;

    protected $rules = [
        'singular_they' => 'nullable|boolean',
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
            if (strpos($variant, 'en') === 0) {
                $this->enabled = true;
            }
        }

        $this->singular_they = (bool) $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'singular_they',
            'english_rules'
        );
    }

    public function updateLanguageGuidelinesEnglish()
    {
        $this->validate();

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $languageGuidelines->singular_they = (bool) $this->singular_they;

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
        return view('livewire.user-guidelines.english');
    }
}
