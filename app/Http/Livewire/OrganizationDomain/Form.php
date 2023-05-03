<?php

namespace App\Http\Livewire\OrganizationDomain;

use App\Models\Domain;
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
    public $model;

    /**
     * Mount the component.
     *
     * @param  mixed  $model
     * @return void
     */
    public function mount($model)
    {
        $this->model = $model;

        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.organization-domain.form', [
            'show' => $this->model->getDomainListType() !== 'allow_witty_works'
        ]);
    }

    public function typeChange()
    {
        return $this->render();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->domain_id = '';
        $this->domain = '';
    }

    public function cancel()
    {
        $this->resetForm();

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

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $this->domain = Domain::validateDomain($this->domain);

        $query = Domain::query()
            ->where('team_id', $this->model->id)
            ->where('domain', $this->domain);

        if ($this->domain_id) {
            $query->whereNot('id', $this->domain_id);
            $domain = Domain::find($this->domain_id);
        }

        if (!empty($domain)) {
            if ($this->model->id !== $domain->team_id) {
                $message = __('guidelines.domain_error');
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
        $domain->team_id = $this->model->id;

        $domain->save();
        $domain->dispatchEventToPosthog();

        $this->emit('saved');
        $this->resetForm();
    }
}
