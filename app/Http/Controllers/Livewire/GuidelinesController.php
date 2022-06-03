<?php

namespace App\Http\Controllers\Livewire;

use App\Http\Controllers\TeamControllerTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class GuidelinesController extends Controller
{
    use TeamControllerTrait;

    public function editOrganizationGuidelines(Request $request)
    {
        $team = $this->getCurrentTeam($request);

        if (!Auth::user()->hasTeamPermission($team, 'read')) {
            abort(403);
        }

        return view('organization-guidelines', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }

    public function editFalsePositives(Request $request)
    {
        $team = $this->getCurrentTeam($request);

        if (!Auth::user()->hasTeamPermission($team, 'read')) {
            abort(403);
        }

        return view('false-positive', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }

    public function editTermReplacements(Request $request)
    {
        $team = $this->getCurrentTeam($request);

        if (!Auth::user()->hasTeamPermission($team, 'read')) {
            abort(403);
        }

        return view('term-replacement', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }
}
