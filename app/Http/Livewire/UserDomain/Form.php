<?php

namespace App\Http\Livewire\UserDomain;

use App\Models\Domain;
use Illuminate\Validation\ValidationException;
use App\Http\Livewire\OrganizationDomain\Form as OrganizationForm;
use Illuminate\Support\Facades\Auth;

class Form extends OrganizationForm
{
    protected $listeners = ['edit'];

    /**
     * The user instance.
     *
     * @var mixed
     */
    public $user;

    /**
     * Mount the component.
     *
     * @param  mixed  $user
     * @return void
     */
    public function mount($user)
    {
        $this->user = Auth::user();
    }

    public function render()
    {
        return view('livewire.user-domain.form');
    }

    public function storeDomain()
    {
        $this->validate();

        $this->domain = Domain::validateDomain($this->domain);

        $query = Domain::query()
            ->where('user_id', $this->user->id)
            ->where('domain', $this->domain);

        if ($this->domain_id) {
            $query->whereNot('id', $this->domain_id);
            $domain = Domain::find($this->domain_id);
        }

        if (!empty($domain)) {
            if ($this->user->id !== $domain->user_id) {
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
        $domain->user_id = $this->user->id;

        $domain->save();

        $this->emit('saved');

        $this->domain_id = '';
        $this->domain = '';
    }
}
