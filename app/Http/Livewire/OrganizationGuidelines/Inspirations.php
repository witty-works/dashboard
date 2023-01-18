<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Http\Livewire\HelpHeroTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Inspirations extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    public $show_inspiration_alternatives;
    public $show_inspiration_alternatives_force;

    protected $rules = [
        'show_inspiration_alternatives' => 'nullable|boolean',
        'show_inspiration_alternatives_force' => 'nullable|boolean',
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

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = $this->getLanguageGuidelines($this->team);

        $this->show_inspiration_alternatives = (bool) $languageGuidelines->show_inspiration_alternatives;
        $this->show_inspiration_alternatives_force = (bool) $languageGuidelines->show_inspiration_alternatives_force;
    }

    public function updateLanguageGuidelinesInspirations()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = $this->getLanguageGuidelines($this->team);

        $languageGuidelines->show_inspiration_alternatives = (bool) $this->show_inspiration_alternatives;
        $languageGuidelines->show_inspiration_alternatives_force = (bool) $this->show_inspiration_alternatives_force;

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
        return view('livewire.organization-guidelines.inspirations');
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }

    protected function getLanguageGuidelines($team)
    {
        $languageGuidelines = LanguageGuidelines::firstOrNew(['team_id' => $team->id]);

        if (!$this->team->subscribed()) {
            $this->show_inspiration_alternatives = false;
            $languageGuidelines->show_inspiration_alternatives = false;
            $this->show_inspiration_alternatives_force = false;
            $languageGuidelines->show_inspiration_alternatives_force = false;
        }

        return $languageGuidelines;
    }
}
