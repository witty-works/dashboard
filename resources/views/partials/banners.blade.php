@php
$user = Auth::user();
$team = $user->currentTeam;

$showInviteCheck = empty($hideInviteCheck) && $team && $team->getTotalUserWithInvitationsCount() <= 1;
$showInvitations = $user->invitations->count();
$showMailing = !$user->has_consented_to_mailing;
@endphp

@include('partials.extension-check')

@if($showInvitations)
@include('partials.invitations', ['user' => $user])
@elseif($showInviteCheck)
@include('partials.invite-check')
@elseif($showMailing)
@include('partials.mailing-consent', ['user' => $user])
@endif
