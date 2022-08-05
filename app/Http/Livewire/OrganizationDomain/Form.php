<?php

namespace App\Http\Livewire\OrganizationDomain;

use App\Models\Domain;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $domain_id;
    public $domain;

    protected $listeners = ['edit', 'typeChange'];

    protected $rules = [
        'domain_id' => 'int|nullable',
        'domain' => 'required|string|max:250',
    ];

    /**
     * The team instance.
     *
     * @var mixed
     */
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
    }

    public function render()
    {
        $languageGuidelines = LanguageGuidelines::firstOrNew(['team_id' => $this->team->id]);

        return view('livewire.organization-domain.form', ['show' => $languageGuidelines->domain_list_type !== 'allow_witty_works']);
    }

    public function typeChange()
    {
        return $this->render();
    }

    public function edit(Domain $domain)
    {
        $this->domain_id = $domain->id;
        $this->domain = $domain->domain;

        return $this->render();
    }

    public function storeDomain()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $this->domain = Domain::validateDomain($this->domain);

        $query = Domain::query()
            ->where('team_id', $this->team->id)
            ->where('domain', $this->domain);

        if ($this->domain_id) {
            $query->whereNot('id', $this->domain_id);
            $domain = Domain::find($this->domain_id);

            if ($this->team->id !== $domain->team_id) {
                $message = __(
                    'guidelines.domain_error',
                );
                throw ValidationException::withMessages(['domain' => $message]);
            }
        } else {
            $domain = new Domain();
        }

        $count = $query->count();
        if ($count) {
            $message = __('guidelines.domain_already_exists');
            throw ValidationException::withMessages(['domain' => $message]);
        }

        $domain->domain = $this->domain;
        $domain->team_id = $this->team->id;

        $domain->save();

        $this->emit('saved');

        $this->domain_id = '';
        $this->domain = '';
    }
}
