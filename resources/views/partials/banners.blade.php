@php

use App\Http\Controllers\OAuthController;

$team = false;
$showInviteCheck = false;
$showInvitations = false;
$showInvitationRequests = false;
$showMailing = false;

$user = Auth::user();
if ($user) {
    $team = $user->currentTeam;
    if ($team) {
        $showInviteCheck = empty($hideInviteCheck) && $team->getTotalUserWithInvitationsCount() <= 1;
        if ($user->hasTeamPermission($team, 'update')) {
            $showInvitationRequests = $team->invitationRequests->isNotEmpty();
        }
    }

    $showInvitations = $user->invitations->count();
    $showMailing = $user->has_consented_to_mailing === null;
}
@endphp

@if (Session::get(OAuthController::LOGIN_SOURCE) === OAuthController::AZURE_AD_B2C_PROVIDER)
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
@elseif($team && !$team->subscribed() && !Route::is('teams.subscription'))
@include('partials.subscribe-banner', ['user' => $user])
@endif
