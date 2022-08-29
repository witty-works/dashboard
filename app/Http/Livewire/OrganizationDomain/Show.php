<?php

namespace App\Http\Livewire\OrganizationDomain;

use App\Models\Domain;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    protected $listeners = ['saved', 'typeChange'];

    public $team;

    public $hide_actions;

    public function mount($team, $hide_actions = False)
    {
        $this->team = $team;
        $this->hide_actions = $hide_actions;
    }

    public function render()
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'read')) {
            abort(403);
        }

        $languageGuidelines = LanguageGuidelines::firstOrNew(['team_id' => $this->team->id]);

        if ($languageGuidelines->domain_list_type === 'allow_witty_works') {
            $list = [
                new Domain(['domain' => 'witty.works'])
            ];
        } else {
            $list = Domain::all()->where('team_id', $this->team->id)->sortByDesc('created_at');
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
    }

    public function editDomain(Domain $domain)
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $this->emit('edit', $domain->id);
    }

    public function deleteDomain(Domain $domain)
    {
        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $domain->delete();
    }
}
