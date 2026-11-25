<?php

namespace App\Actions\Account;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Contracts\DeletesUsers;

/**
 * Delete the signed-in user's account and end their session. Used by the
 * profile page's form and by the emailed confirmation link.
 */
class DeleteOwnAccount
{
    public function __construct(protected DeletesUsers $deleter)
    {
    }

    public function delete(Request $request, User $user): void
    {
        $this->deleter->delete($user->fresh());

        // Not logout(): with a remember token it saves a new one on the user,
        // which no longer exists, and that save queues syncs for them. The
        // emailed link can also be used without being signed in.
        $guard = Auth::guard(config('fortify.guard'));
        if ($guard->check()) {
            $guard->logoutCurrentDevice();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
