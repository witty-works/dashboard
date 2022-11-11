<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Http\Livewire\HelpHeroTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class English extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    public $singular_they;
    public $english_rules_force;

    protected $rules = [
        'singular_they' => 'nullable|boolean',
        'english_rules_force' => 'nullable|boolean',
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

        $languageGuidelines = $this->getLanguageGuidelines($this->team);

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
        $languageGuidelines->english_rules_force = (bool) $this->english_rules_force;

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
        return view('livewire.organization-guidelines.english');
    }

    protected function getLanguageGuidelines($team)
    {
        return LanguageGuidelines::firstOrNew(['team_id' => $team->id]);
    }
}
