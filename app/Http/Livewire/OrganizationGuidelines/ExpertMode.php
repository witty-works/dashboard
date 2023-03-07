<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExpertMode extends Component
{
    use AuthorizesRequests;
    use GuidelineTrait;

    public $expert_mode;
    public $simple_language;
    public $expert_mode_force;

    protected $rules = [
        'expert_mode' => 'nullable|boolean',
        'simple_language' => 'nullable|boolean',
        'expert_mode_force' => 'nullable|boolean',
    ];

    public $team;

    public function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = $this->getLanguageGuidelines($this->team);

        $this->expert_mode = (bool) $languageGuidelines->expert_mode;
        $this->simple_language = (bool) $languageGuidelines->simple_language;
        $this->expert_mode_force = (bool) $languageGuidelines->expert_mode_force;
    }

    public function updateLanguageGuidelinesExpertMode()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        if ($this->team->subscribed()) {
            $languageGuidelines = $this->getLanguageGuidelines($this->team);

            $languageGuidelines->simple_language = (bool) $this->simple_language;
            if ($languageGuidelines->simple_language) {
                $this->expert_mode = true;
            }
            $languageGuidelines->expert_mode = (bool) $this->expert_mode;
            $languageGuidelines->expert_mode_force = (bool) $this->expert_mode_force;

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
        return view('livewire.organization-guidelines.expert-mode');
    }
}
