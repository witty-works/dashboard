@php
$team = false;
$showInviteCheck = false;
$showInvitations = false;
$showMailing = false;

$user = Auth::user();
if ($user) {
    $team = $user->currentTeam;

    $showInviteCheck = empty($hideInviteCheck) && $team && $team->getTotalUserWithInvitationsCount() <= 1;
    $showInvitations = $user->invitations->count();
    $showMailing = $user->has_consented_to_mailing === null;
}
@endphp

@include('partials.extension-check')

@if($showInvitations)
@include('partials.invitations', ['user' => $user])
@elseif($showInviteCheck)
@include('partials.invite-check')
@elseif($showMailing && !Route::is('profile.show'))
@include('partials.mailing-consent', ['user' => $user])
@elseif($team && !$team->subscribed() && !Route::is('teams.subscription'))
@include('partials.subscribe-banner', ['user' => $user])
@endif
