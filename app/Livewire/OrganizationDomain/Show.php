<?php

namespace App\Livewire\OrganizationDomain;

use App\Livewire\HelpHeroTrait;
use App\Models\Domain;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

    protected $listeners = ['saved', 'typeChange'];

    public $model;

    public $hide_actions;

    public function mount($model, $hide_actions = false)
    {
        $this->model = $model;
        $this->hide_actions = $hide_actions;
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user->hasTeamPermission($this->model, 'read')) {
            abort(403);
        }

        $languageGuidelines = LanguageGuidelines::getTeamGuidelines($user);
        if ($languageGuidelines->domain_list_type === 'allow_witty_works') {
            $list = new Collection([
                new Domain(['domain' => 'witty.works'])
            ]);
        } else {
            $list = Domain::all()->where('team_id', $this->model->id)->sortByDesc('created_at');
        }

        return view('livewire.organization-domain.show', [
            'list' => $list,
            'domain_list_type' => $languageGuidelines->domain_list_type,
        ]);
    }

    public function typeChange()
    {
        return $this->render();
    }

    public function saved()
    {
        $this->render();
        $this->updateHelpHero();
    }

    public function editDomain(Domain $domain)
    {
        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $this->dispatch('edit', domain: $domain);
    }

    public function deleteDomain(Domain $domain)
    {
        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $domain->delete();
        $this->updateHelpHero();
    }
}
