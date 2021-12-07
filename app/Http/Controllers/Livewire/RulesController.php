<?php

namespace App\Http\Controllers\Livewire;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;

class RulesController extends Controller
{
    public function editCorporateRules(Request $request, $teamId)
    {
        $team = Jetstream::newTeamModel()->findOrFail($teamId);

        if (!Auth::user()->hasTeamPermission($team, 'read')) {
            abort(403);
        }

        return view('corporate-rules', [
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
}
