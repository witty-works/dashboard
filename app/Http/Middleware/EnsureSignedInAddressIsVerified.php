<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;

/**
 * Sends a signed-in user whose address is not verified to the verification
 * page. Unlike Laravel's `verified`, a guest goes through, for routes that
 * handle guests themselves (the OAuth authorization endpoint sends them to
 * the login screen and back).
 */
class EnsureSignedInAddressIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            // Remembered, so verifying leads back here (Fortify redirects
            // to the intended URL), and the extension's sign-in goes on.
            return redirect()->guest(route('verification.notice'));
        }

        return $next($request);
    }
}
