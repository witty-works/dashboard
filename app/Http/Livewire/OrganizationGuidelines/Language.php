<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Http\Livewire\HelpHeroTrait;
use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Language extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    public $preferred_variants;
    public $preferred_variants_force;
    public $preferred_variants_de;
    public $preferred_variants_en;

    protected $rules = [
        'preferred_variants_force' => 'nullable|boolean',
        'preferred_variants_de' => 'nullable|string|in:both,de-DE,de-AT,de-CH',
        'preferred_variants_en' => 'nullable|string|in:both,en-US,en-GB',
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

        $this->preferred_variants = (array) $languageGuidelines->preferred_variants;
        $this->preferred_variants_force = (bool) $languageGuidelines->preferred_variants_force;

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

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = $this->getLanguageGuidelines($this->team);

        $this->preferred_variants = [];
        foreach (GuidelinesInterface::LANGUAGES as $lang) {
            $property = "preferred_variants_$lang";
            if ($this->$property) {
                $this->preferred_variants[] = $this->$property;
            }
        }

        $languageGuidelines->preferred_variants = $this->preferred_variants;
        $languageGuidelines->preferred_variants_force = $this->preferred_variants_force;

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
        return view('livewire.organization-guidelines.language');
    }

    protected function getLanguageGuidelines($team)
    {
        return LanguageGuidelines::firstOrNew(['team_id' => $team->id]);
    }
}
