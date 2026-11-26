<?php

namespace App\Http\Middleware;

use App\Support\InviteOnly;
use Closure;
use Illuminate\Http\Request;

/**
 * Invite only: the dashboard's pages need a team. A user without one sees
 * their pending invitations, a superadmin goes to /admin. Applied to the
 * signed-in page group only, so verifying an address, signing out and
 * Livewire requests are not affected.
 */
class EnsureUserHasTeam
{
    /**
     * Pages a user without a team still needs. The localizer registers each
     * route under a prefixed name (with_locale., without_locale.).
     */
    private const ALLOWED = [
        'profile.show', '*.profile.show',
        'user.mailing_consent', '*.user.mailing_consent',
        'team-invitations.*', '*.team-invitations.*',
        'no-team', '*.no-team',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! InviteOnly::enabled() || $user === null || $user->currentTeam || $request->routeIs(...self::ALLOWED)) {
            return $next($request);
        }

        return $user->can(config('admin.permission'))
            ? redirect()->route('admin.home')
            : redirect()->route('no-team');
    }
}
