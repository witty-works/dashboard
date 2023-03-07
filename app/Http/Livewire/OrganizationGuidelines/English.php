<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class English extends Component
{
    use AuthorizesRequests;
    use GuidelineTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $singular_they;
    public $english_rules_force;

    protected $rules = [
        'singular_they' => 'nullable|boolean',
        'english_rules_force' => 'nullable|boolean',
    ];

    public $team;

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->enabled = false;

        $languageGuidelines = $this->getLanguageGuidelines($this->team);
        foreach ($languageGuidelines->preferred_variants as $variant) {
            if (strpos($variant, 'en') === 0) {
                $this->enabled = true;
            }
        }

        $this->singular_they = (bool) $languageGuidelines->singular_they;
        $this->english_rules_force = (bool) $languageGuidelines->english_rules_force;
    }

    public function updateLanguageGuidelinesEnglish()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = $this->getLanguageGuidelines($this->team);

        $languageGuidelines->singular_they = (bool) $this->singular_they;
        if ($this->team->subscribed()) {
            $languageGuidelines->english_rules_force = (bool) $this->english_rules_force;
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
        return view('livewire.organization-guidelines.english');
    }
}
