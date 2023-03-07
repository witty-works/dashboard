<?php

namespace App\Http\Livewire\UserGuidelines;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ExpertMode extends Component
{
    use AuthorizesRequests;
    use GuidelineTrait;

    public $expert_mode;
    public $simple_language;

    protected $rules = [
        'expert_mode' => 'nullable|boolean',
        'simple_language' => 'nullable|boolean',
    ];

    public $user;

    protected function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $this->expert_mode = (bool) $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'expert_mode'
        );

        $this->simple_language = (bool) $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'simple_language',
            'expert_mode'
        );
    }

    public function updateLanguageGuidelinesExpertMode()
    {
        $this->validate();

        if ($this->user->subscribed()) {
            $languageGuidelines = $this->getLanguageGuidelines($this->user);

            $languageGuidelines->simple_language = (bool) $this->simple_language;
            if ($languageGuidelines->simple_language) {
                $this->expert_mode = true;
            }
            $languageGuidelines->expert_mode = (bool) $this->expert_mode;

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
        return view('livewire.user-guidelines.expert-mode');
    }
}
