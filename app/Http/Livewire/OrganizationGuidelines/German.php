<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Http\Livewire\HelpHeroTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class German extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    protected $listeners = ['saved'];

    public $enabled;
    public $german_rules_force;
    public $german_gender_ending;
    public $gendered_roles_format;

    protected $rules = [
        'german_rules_force' => 'nullable|boolean',
        'german_gender_ending' => 'nullable|string|in::in,*in,/in,_in,In,/-in',
        'gendered_roles_format' => 'nullable|string|in:both,inclusive_gender,binary_gender,none',
    ];

    public $team;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;
        $this->enabled = false;

        $languageGuidelines = $this->getLanguageGuidelines($this->team);
        foreach ($languageGuidelines->preferred_variants as $variant) {
            if (strpos($variant, 'de') === 0) {
                $this->enabled = true;
            }
        }

        $this->german_rules_force = $languageGuidelines->german_rules_force;
        $this->german_gender_ending = $languageGuidelines->german_gender_ending;
        $this->gendered_roles_format = $languageGuidelines->gendered_roles_format;
    }

    public function updateLanguageGuidelinesGerman()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = $this->getLanguageGuidelines($this->team);

        $languageGuidelines->german_rules_force = $this->german_rules_force;
        $languageGuidelines->german_gender_ending = $this->german_gender_ending;
        $languageGuidelines->gendered_roles_format = $this->gendered_roles_format;

        $languageGuidelines->save();

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
        return view('livewire.organization-guidelines.german');
    }

    public function saved()
    {
        $this->mount($this->team);
        $this->render();
    }

    protected function getLanguageGuidelines($team)
    {
        $languageGuidelines = LanguageGuidelines::firstOrNew(['team_id' => $team->id]);

        if (!$this->team->subscribed()) {
            $this->gendered_roles_format = 'both';
            $languageGuidelines->gendered_roles_format = 'both';
        }

        return $languageGuidelines;
    }
}
