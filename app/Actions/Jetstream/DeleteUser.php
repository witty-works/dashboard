<?php

namespace App\Actions\Jetstream;

use App\Models\Domain;
use App\Models\FalsePositive;
use App\Models\Kpi;
use App\Models\LanguageGuidelines;
use App\Models\TermReplacement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\DeletesTeams;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * The team deleter implementation.
     *
     * @var \Laravel\Jetstream\Contracts\DeletesTeams
     */
    protected $deletesTeams;

    /**
     * Create a new action instance.
     *
     * @param  \Laravel\Jetstream\Contracts\DeletesTeams  $deletesTeams
     * @return void
     */
    public function __construct(DeletesTeams $deletesTeams)
    {
        $this->deletesTeams = $deletesTeams;
    }

    /**
     * Delete the given user and everything that is theirs. The tables that
     * hold it have no cascading foreign keys, so each is cleared here.
     *
     * @param  mixed  $user
     * @return void
     */
    public function delete($user)
    {
        DB::transaction(function () use ($user) {
            // Locked, so that a team handed to the user meanwhile is either
            // seen here or waits until the deletion is done.
            $this->ensureNoTeamToHandOver($user, $user->ownedTeams()->lockForUpdate()->get());

            $this->deleteTeams($user);

            foreach ([LanguageGuidelines::class, FalsePositive::class, TermReplacement::class, Domain::class, Kpi::class] as $model) {
                $model::where('user_id', $user->id)->delete();
            }

            $user->connectedAccounts->each->delete();

            // By user_id rather than Passport's tokens() relation, which
            // filters by client and could leave some behind.
            $tokenIds = DB::table('oauth_access_tokens')->where('user_id', $user->id)->pluck('id');
            DB::table('oauth_refresh_tokens')->whereIn('access_token_id', $tokenIds)->delete();
            DB::table('oauth_access_tokens')->where('user_id', $user->id)->delete();
            DB::table('oauth_auth_codes')->where('user_id', $user->id)->delete();
            DB::table('oauth_device_codes')->where('user_id', $user->id)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_resets')->where('email', $user->email)->delete();

            $user->delete();
        });
    }

    /**
     * A team the user owns is deleted with them, so one that has other
     * members has to be handed over to one of its admins first.
     *
     * @param  mixed  $user
     * @return void
     */
    protected function ensureNoTeamToHandOver($user, $ownedTeams)
    {
        $messages = [];

        $withMembers = $ownedTeams->filter(fn ($team) => $team->users()->exists())->pluck('name');
        if ($withMembers->isNotEmpty()) {
            $messages[] = __('content.delete_account_hand_over_teams', ['teams' => $withMembers->join(', ')]);
        }

        // A team waiting for its owner has an invitation but no members;
        // deleting it would silently drop the invitation with it.
        $withInvitations = $ownedTeams->filter(fn ($team) => $team->teamInvitations()->exists())->pluck('name');
        if ($withInvitations->isNotEmpty()) {
            $messages[] = __('content.delete_account_open_invitations', ['teams' => $withInvitations->join(', ')]);
        }

        if ($messages) {
            throw ValidationException::withMessages(['team' => $messages]);
        }
    }

    /**
     * Delete the teams and team associations attached to the user.
     *
     * @param  mixed  $user
     * @return void
     */
    protected function deleteTeams($user)
    {
        $user->teams()->detach();

        $user->ownedTeams->each(function ($team) {
            $this->deletesTeams->delete($team);
        });
    }
}
