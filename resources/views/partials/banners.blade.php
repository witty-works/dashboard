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
        $hideInviteCheck = !$team->subscribed() && empty($hideInviteCheck);
        $showInviteCheck = $hideInviteCheck && $team->getTotalUserWithInvitationsCount() <= 1;
        if ($user->hasTeamPermission($team, 'update')) {
            $showInvitationRequests = $team->invitationRequests->isNotEmpty();
        }
    }

    $showInvitations = $user->invitations->count();
    $showMailing = $user->has_consented_to_mailing === null;
}
@endphp

@if (Session::get(\App\Http\Controllers\OAuthController::LOGIN_SOURCE) !== \App\Http\Controllers\OAuthController::OFFICE_PROVIDER)
@include('partials.extension-check')
@endif

@if(!Route::is('teams.subscription'))
    @if($team->subscribed())
        @if($team->subscription()->ended())
            @include('partials.subscription-ended', ['user' => $user])
        @endif
    @elseif($team->hasExpiredGenericTrial())
        @include('partials.trial-ended', ['user' => $user])
    @elseif($team->onGenericTrial())
        @include('partials.on-trial', ['user' => $user])
    @endif
@endif

@if($showInvitations)
@include('partials.invitations', ['user' => $user])
@elseif($showInvitationRequests && !Route::is('teams.show'))
@include('partials.invitation-requests')
@elseif($showInviteCheck)
@include('partials.invite-check')
@elseif($showMailing && !Route::is('profile.show'))
@include('partials.mailing-consent', ['user' => $user])
@elseif($team && !$team->subscribed() && !$team->onGenericTrial() && !$team->hasExpiredGenericTrial() && !Route::is('teams.subscription'))
@include('partials.subscribe-banner', ['user' => $user])
@endif
