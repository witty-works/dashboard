<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\SuperadminTwoFactor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;

/**
 * Lets back in someone who lost both their authenticator app and their
 * recovery codes: removes two-factor sign-in from the account. Run it only
 * after confirming who is asking (docs/user-management.md). A superadmin then
 * has to set it up again before using /admin.
 */
class ResetTwoFactorCommand extends Command
{
    protected $signature = 'two-factor:reset {email}';

    protected $description = 'Remove two-factor sign-in from an account whose owner lost their authenticator and recovery codes';

    public function handle(DisableTwoFactorAuthentication $disable): int
    {
        $email = strtolower(trim($this->argument('email')));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            $this->error("No account with the address {$email}.");

            return self::FAILURE;
        }

        if ($user->two_factor_secret === null && $user->two_factor_confirmed_at === null) {
            $this->info("{$user->email} does not use two-factor sign-in; nothing to reset.");

            return self::SUCCESS;
        }

        if (! $this->confirm("Remove two-factor sign-in from {$user->email}?")) {
            $this->line('Nothing changed.');

            return self::FAILURE;
        }

        $disable($user);
        Log::notice('Two-factor sign-in reset from the command line', ['user' => $user->id]);

        $this->info("Two-factor sign-in removed from {$user->email}. They sign in with their password alone until they set it up again.");
        if (SuperadminTwoFactor::applies($user)) {
            $this->line('They are a superadmin: they must set up two-factor sign-in again on their profile page before using /admin.');
        }

        return self::SUCCESS;
    }
}
