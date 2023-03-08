<?php

namespace App\Http\Livewire\UserGuidelines;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Inspirations extends Component
{
    use AuthorizesRequests;
    use GuidelineTrait;

    public $show_inspiration_alternatives;

    protected $rules = [
        'show_inspiration_alternatives' => 'nullable|boolean',
    ];

    public $user;

    protected function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $this->show_inspiration_alternatives = (bool) $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'show_inspiration_alternatives'
        );
    }

    public function updateLanguageGuidelinesInspirations()
    {
        $this->validate();

        if ($this->user->subscribed()) {
            $languageGuidelines = $this->getLanguageGuidelines($this->user);
            $languageGuidelines->show_inspiration_alternatives = (bool) $this->show_inspiration_alternatives;

            $languageGuidelines->save();
            $languageGuidelines->dispatchEventToPosthog((new \ReflectionClass($this))->getShortName());
        }

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
        return view('livewire.user-guidelines.inspirations');
    }
}
