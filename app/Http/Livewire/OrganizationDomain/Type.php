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

    public function storeDomainType()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $languageGuidelines->domain_list_type = $this->type;

        $languageGuidelines->save();

        $this->emit('typeChange');
        $this->emit('saved');
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

        $this->type = $this->model->getDomainListType();
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }
}
