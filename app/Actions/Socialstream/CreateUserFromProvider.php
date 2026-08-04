<?php

namespace App\Actions\Socialstream;

use App\Http\Middleware\SwitchToTeam;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Contracts\SocialAuth\CreatesConnectedAccounts;
use App\Contracts\SocialAuth\CreatesUserFromProvider;
use Laravel\Socialite\Contracts\User as ProviderUserContract;

class CreateUserFromProvider implements CreatesUserFromProvider
{
    /**
     * The creates connected accounts instance.
     *
     * @var \App\Contracts\SocialAuth\CreatesConnectedAccounts
     */
    public $createsConnectedAccounts;

    /**
     * Create a new action instance.
     *
     * @param  \App\Contracts\SocialAuth\CreatesConnectedAccounts  $createsConnectedAccounts
     */
    public function __construct(CreatesConnectedAccounts $createsConnectedAccounts)
    {
        $this->createsConnectedAccounts = $createsConnectedAccounts;
    }

    /**
     * Create a new user from a social provider user.
     *
     * @param  string  $provider
     * @param  \Laravel\Socialite\Contracts\User  $providerUser
     * @return \App\Models\User
     */
    public function create(string $provider, ProviderUserContract $providerUser): mixed
    {
        return DB::transaction(function () use ($provider, $providerUser) {
            return tap(User::create([
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
            ]), function (User $user) use ($provider, $providerUser) {
                $user->markEmailAsVerified();

                $this->createsConnectedAccounts->create($user, $provider, $providerUser);

                SwitchToTeam::ensureTeam($user);

                $user->applyAcceptedInvitiations();

                if ($user->currentTeam && !$user->currentTeam->subscription() && !$user->currentTeam->trial_ends_at) {
                    $user->currentTeam->trial_ends_at = Carbon::now()->addDays(config('cashier.trail_days'))->format('Y-m-d') . ' 23:59:59';
                    $user->currentTeam->save();
                }
            });
        });
    }
}
