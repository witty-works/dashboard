<?php

namespace App\Http\Livewire\OrganizationDomain;

use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Type extends Component
{
    use AuthorizesRequests;

    public $type;

    protected $rules = [
        'type' => 'required|string|in:allow,allow_witty_works,deny',
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

    public function storeDomainType()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = $this->getLanguageGuidelines($this->team);

        $languageGuidelines->domain_list_type = $this->type;

        $languageGuidelines->save();

        $this->emit('typeChange');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.organization-domain.type');
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->type = $this->team->getDomainListType();
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }

    protected function getLanguageGuidelines($team)
    {
        return LanguageGuidelines::firstOrNew(['team_id' => $team->id]);
    }
}
