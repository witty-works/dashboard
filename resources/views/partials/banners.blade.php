@php
$team = false;
$showInviteCheck = false;
$showInvitations = false;
$showInvitationRequests = false;
$showMailing = false;

$user = Auth::user();
if ($user) {
    $team = $user->currentTeam;
    if ($team) {
        // This used to be limited to unsubscribed teams. Billing is gone, so the
        // nudge now goes to any team that has not invited anyone yet.
        $showInviteCheck = empty($hideInviteCheck) && $team->getTotalUserWithInvitationsCount() <= 1;
        if ($user->hasTeamPermission($team, 'update')) {
            $showInvitationRequests = $team->invitationRequests->isNotEmpty();
        }
    }

    $showInvitations = $user->invitations->count();
    $showMailing = $user->has_consented_to_mailing === null;
}
@endphp

@if (config('app.browsers') && Session::get(\App\Http\Controllers\OAuthController::LOGIN_SOURCE) !== \App\Http\Controllers\OAuthController::OFFICE_PROVIDER)
@include('partials.extension-check')
@endif

@if($showInvitations)
@include('partials.invitations', ['user' => $user])
@elseif($showInvitationRequests && !Route::is('teams.show'))
@include('partials.invitation-requests')
@elseif($showInviteCheck)
@include('partials.invite-check')
@elseif($showMailing && !Route::is('profile.show'))
@include('partials.mailing-consent', ['user' => $user])
@endif
