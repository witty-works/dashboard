<?php

namespace App\Livewire\Profile;

use App\Actions\Account\DeleteOwnAccount;
use App\Mail\ConfirmAccountDeletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Deleting one's own account, on the profile page. Like Jetstream's form,
 * it confirms with the password. An account without one, such as one created through
 * Office sign-in, is sent a confirmation link by email instead: the email
 * address is shown on this page, so typing it would prove nothing.
 */
class DeleteAccountForm extends Component
{
    /** Whether the password dialog is open. */
    public $confirmingUserDeletion = false;

    /** The password typed into the dialog. */
    public $password = '';

    /** Whether the confirmation link has been sent. */
    public $confirmationSent = false;

    private const MAX_ATTEMPTS = 5;

    public function confirmUserDeletion()
    {
        $this->resetErrorBag();
        $this->password = '';
        $this->dispatch('confirming-delete-user');
        $this->confirmingUserDeletion = true;
    }

    public function deleteUser(Request $request, DeleteOwnAccount $deleteOwnAccount)
    {
        $this->resetErrorBag();

        $user = Auth::user();

        if (blank($user->password)) {
            $messages = $this->handOverMessages();
            if ($messages) {
                throw ValidationException::withMessages(['team' => $messages]);
            }

            $mailKey = 'delete-account-mail:' . $user->id;
            if (RateLimiter::tooManyAttempts($mailKey, 1)) {
                throw ValidationException::withMessages([
                    'password' => [__('content.delete_account_too_many_attempts', ['seconds' => RateLimiter::availableIn($mailKey)])],
                ]);
            }
            RateLimiter::hit($mailKey, 60);

            Mail::to($user)->queue(new ConfirmAccountDeletion($user));
            $this->confirmationSent = true;
            $this->confirmingUserDeletion = false;

            return null;
        }

        $key = 'delete-account:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'password' => [__('content.delete_account_too_many_attempts', ['seconds' => RateLimiter::availableIn($key)])],
            ]);
        }

        if (! Hash::check((string) $this->password, $user->password)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'password' => [__('content.delete_account_password_mismatch')],
            ]);
        }

        $deleteOwnAccount->delete($request, $user);

        return redirect(config('fortify.redirects.logout') ?? '/');
    }

    /** The teams the user owns that others belong to: these are handed over first. */
    public function getTeamsToHandOverProperty()
    {
        return Auth::user()->ownedTeams()->whereHas('users')->get();
    }

    /** Owned teams with open invitations, such as a team waiting for its owner. */
    public function getTeamsWithOpenInvitationsProperty()
    {
        return Auth::user()->ownedTeams()->whereHas('teamInvitations')->get();
    }

    /** Why the account cannot be deleted yet, if it cannot. */
    public function handOverMessages(): array
    {
        $messages = [];
        if ($this->teamsToHandOver->isNotEmpty()) {
            $messages[] = __('content.delete_account_hand_over_teams', ['teams' => $this->teamsToHandOver->pluck('name')->join(', ')]);
        }
        if ($this->teamsWithOpenInvitations->isNotEmpty()) {
            $messages[] = __('content.delete_account_open_invitations', ['teams' => $this->teamsWithOpenInvitations->pluck('name')->join(', ')]);
        }

        return $messages;
    }

    public function render()
    {
        return view('profile.delete-user-form', [
            'confirmByEmail' => blank(Auth::user()->password),
        ]);
    }
}
