<?php

namespace App\Actions\Fortify;

use App\Support\InviteOnly;
use App\Http\Middleware\SwitchToTeam;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255', 'unique:users',
                function ($attribute, $value, $fail) {
                    if (! InviteOnly::allowsSignUp($value)) {
                        $fail(__('content.invite_only_sign_up_refused'));
                    }
                },
            ],
            'password' => $this->passwordRules(),
            // Only when the deployment provides something to agree to.
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() && \App\Support\LegalDocument::anyProvided() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make(Str::random(20)),
            ]), function (User $user) {
                SwitchToTeam::ensureTeam($user);
            });
        });
    }
}
