<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

trait TeamControllerTrait
{
    protected function getCurrentTeam(Request $request)
    {
        $user = $request->user();
        $team = $user->currentTeam;
        if (!$team) {
            abort(404);
        }

        return $team;
    }
}
