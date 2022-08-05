<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExpertMode extends Component
{
    use AuthorizesRequests;

    public $expert_mode;
    public $expert_mode_force;

    protected $rules = [
        'expert_mode' => 'nullable|boolean',
        'expert_mode_force' => 'nullable|boolean',
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

        $this->expert_mode = (bool) $languageGuidelines->expert_mode;
        $this->expert_mode_force = (bool) $languageGuidelines->expert_mode_force;
    }

    public function updateLanguageGuidelinesExpertMode()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = $this->getLanguageGuidelines($this->team);

        $languageGuidelines->expert_mode = (bool) $this->expert_mode;
        $languageGuidelines->expert_mode_force = (bool) $this->expert_mode_force;

        $languageGuidelines->save();

        $this->emit('saved');
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

    protected function getLanguageGuidelines($team)
    {
        $languageGuidelines = LanguageGuidelines::firstOrNew(['team_id' => $team->id]);

        if (!$this->team->subscribed()) {
            $this->expert_mode = false;
            $languageGuidelines->expert_mode = false;
            $this->expert_mode_force = false;
            $languageGuidelines->expert_mode_force = false;
        }

        return $languageGuidelines;
    }
}
