<?php

namespace App\Http\Controllers\Livewire;

use App\Http\Controllers\TeamControllerTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;

class TeamController extends Controller
{
    use TeamControllerTrait;

    public function show(Request $request)
    {
        $team = $this->getCurrentTeam($request);

        if (Gate::denies('view', $team)) {
            abort(403);
        }

        if (Gate::denies('update', $team)) {
            return redirect()->route('profile.show');
        }

        return view('teams.show', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }

    /**
     * Show the team creation screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Jetstream::newTeamModel());

        return view('teams.create', [
            'user' => $request->user(),
        ]);
    }
}
