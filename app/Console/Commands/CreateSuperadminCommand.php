<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * The way into a deployment whose sign-up is closed (INVITE_ONLY): run on
 * the server, it creates a verified superadmin and mails them a link to set
 * a password, or makes an existing account a superadmin.
 */
class CreateSuperadminCommand extends Command
{
    protected $signature = 'superadmin:create {email} {name}';

    protected $description = 'Create a superadmin, or make an existing account one';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $name = trim($this->argument('name'));

        $validator = Validator::make(compact('email', 'name'), [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            $this->error(implode(' ', $validator->errors()->all()));

            return self::FAILURE;
        }

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($user) {
            // Anyone can register any address, so an unverified account may
            // not belong to the person the role is meant for.
            if (! $user->hasVerifiedEmail()) {
                $this->error("{$user->email} has not verified its address; verify it first or delete the account.");

                return self::FAILURE;
            }

            $user->assignRole(config('admin.role'));
            $this->info("{$user->email} is now a superadmin.");

            return self::SUCCESS;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            // Unusable until they set one through the link.
            'password' => Hash::make(Str::random(64)),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole(config('admin.role'));

        $status = Password::sendResetLink(['email' => $email]);
        if ($status !== Password::RESET_LINK_SENT) {
            $this->warn("Created {$email}, but the password link was not sent: " . __($status));

            return self::SUCCESS;
        }

        $this->info("Created superadmin {$email} and mailed a link to set a password.");

        return self::SUCCESS;
    }
}
