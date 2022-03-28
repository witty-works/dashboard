<?php

namespace App\Http\Controllers\Livewire;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Jetstream;

class GuidelinesController extends Controller
{
    public function editOrganizationGuidelines(Request $request, $teamId)
    {
        $team = Jetstream::newTeamModel()->findOrFail($teamId);

        if (!Auth::user()->hasTeamPermission($team, 'read')) {
            abort(403);
        }

        return view('organization-guidelines', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }

    public function editFalsePositives(Request $request, $teamId)
    {
        $team = Jetstream::newTeamModel()->findOrFail($teamId);

        if (!Auth::user()->hasTeamPermission($team, 'read')) {
            abort(403);
        }

        return view('false-positive', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }

    public function editTermReplacements(Request $request, $teamId)
    {
        $team = Jetstream::newTeamModel()->findOrFail($teamId);

        if (!Auth::user()->hasTeamPermission($team, 'read')) {
            abort(403);
        }

        return view('term-replacement', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }
}
