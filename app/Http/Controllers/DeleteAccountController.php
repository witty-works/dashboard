<?php

namespace App\Http\Controllers;

use App\Actions\Account\DeleteOwnAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * The emailed link for deleting an account without a password. The signed
 * link proves the mailbox, so no session is needed; someone signed in as
 * another user is refused. Opening it only shows a button, so a mail scanner
 * that follows links deletes nothing; the button posts back to the same
 * signed address.
 */
class DeleteAccountController extends Controller
{
    public function show(Request $request, User $user)
    {
        $this->ensureNotSomeoneElse($request, $user);

        return view('profile.confirm-account-deletion', ['action' => $request->fullUrl()]);
    }

    public function destroy(Request $request, User $user, DeleteOwnAccount $deleteOwnAccount)
    {
        $this->ensureNotSomeoneElse($request, $user);

        try {
            $deleteOwnAccount->delete($request, $user);
        } catch (ValidationException $e) {
            // A team to hand over first. Say so here: redirecting back
            // would lose the reason.
            return response(view('profile.confirm-account-deletion', [
                'action' => $request->fullUrl(),
                'reason' => collect($e->errors())->flatten()->first(),
            ]), 422);
        }

        return redirect(config('fortify.redirects.logout') ?? '/');
    }

    protected function ensureNotSomeoneElse(Request $request, User $user): void
    {
        abort_if($request->user() !== null && ! $request->user()->is($user), 403);
    }
}
