<?php

namespace App\Http\Middleware;

use App\Support\SuperadminTwoFactor;
use Closure;
use Illuminate\Http\Request;

/**
 * Guards /admin and impersonation: a superadmin without confirmed two-factor
 * sign-in is sent to set it up on the profile page; one whose session has
 * not seen a code is asked for one, then sent on. Everyone else passes; the
 * permission itself is checked separately (can:manage users, canImpersonate).
 */
class EnsureSuperadminTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (SuperadminTwoFactor::needsSetup($user)) {
            return redirect()->route('profile.show')->with([
                'flash.banner' => __('content.superadmin_two_factor_required'),
                'flash.bannerStyle' => 'danger',
            ]);
        }

        if (SuperadminTwoFactor::needsCode($user)) {
            return redirect()->guest(route('two-factor.verify'));
        }

        return $next($request);
    }
}
