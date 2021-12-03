<?php

namespace App\Http\Livewire\CorporateRules;

use App\Models\CorporateRules;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $german_gender_ending;
    public $gendered_roles_format;
    public $store_context;

    protected $rules = [
        'german_gender_ending' => 'nullable|string',
        'gendered_roles_format' => 'nullable|string',
        'store_context' => 'nullable|boolean',
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

        $corporateRule = $this->getCorporateRules($this->team);

        $this->german_gender_ending = $corporateRule->german_gender_ending;
        $this->gendered_roles_format = $corporateRule->gendered_roles_format;
        $this->store_context = (bool) $corporateRule->store_context;
    }

    public function updateCorporateRules()
    {
        $this->validate();

        $this->authorize('update', $this->team);

        $corporateRule = $this->getCorporateRules($this->team);

        $corporateRule->german_gender_ending = $this->german_gender_ending;
        $corporateRule->gendered_roles_format = $this->gendered_roles_format;
        $corporateRule->store_context = (bool) $this->store_context;

        $corporateRule->save();

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.corporate-rules.form');
    }

    protected function getCorporateRules($team)
    {
        $corporateRule = CorporateRules::firstOrNew(['team_id' => $team->id]);

        return $corporateRule;
    }
}
