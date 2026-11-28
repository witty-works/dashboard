<?php

namespace App\Actions\Jetstream;

use App\Jobs\RevokeApiKeysInNlpApi;
use App\Models\Domain;
use App\Models\FalsePositive;
use App\Models\Kpi;
use App\Models\LanguageGuidelines;
use App\Models\TermReplacement;
use App\Models\User;
use Laravel\Jetstream\Contracts\DeletesTeams;
use Laravel\Jetstream\Events\TeamMemberRemoved;

class DeleteTeam implements DeletesTeams
{
    /**
     * Delete the given team.
     *
     * @param  mixed  $team
     * @return void
     */
    public function delete($team)
    {
        foreach ($team->allUsers() as $user) {
            TeamMemberRemoved::dispatch($team, $user);
        }

        // Every key for the team, including those of people who left it while
        // a revocation failed.
        dispatch(new RevokeApiKeysInNlpApi(null, $team->posthogId()));

        // The guideline and usage tables have no cascading foreign keys.
        foreach ([LanguageGuidelines::class, FalsePositive::class, TermReplacement::class, Domain::class, Kpi::class] as $model) {
            $model::where('team_id', $team->id)->delete();
        }

        // A licence that points at a deleted team breaks its holder's pages.
        User::where('license_team_id', $team->id)->update(['license_team_id' => null]);

        $team->purge();
    }
}
