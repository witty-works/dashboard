@php
$team = false;
$showInviteCheck = false;
$showInvitations = false;
$showMailing = false;

$user = Auth::user();
if ($user) {
    $team = $user->currentTeam;
    if ($team) {
        // This used to be limited to unsubscribed teams. Billing is gone, so the
        // nudge now goes to any team that has not invited anyone yet.
        $showInviteCheck = empty($hideInviteCheck) && $team->getTotalUserWithInvitationsCount() <= 1;
    }

    $showInvitations = $user->invitations->count();
    $showMailing = $user->has_consented_to_mailing === null;
}
@endphp

@if($showInvitations)
@include('partials.invitations', ['user' => $user])
@elseif($showInviteCheck)
@include('partials.invite-check')
@elseif($showMailing && !Route::is('profile.show'))
@include('partials.mailing-consent', ['user' => $user])
@endif
