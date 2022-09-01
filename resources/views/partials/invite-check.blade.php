<?php
use Illuminate\Support\Facades\Auth;

$teamExists = Auth::user()->currentTeam;
$noInvitesSent = Auth::user()->currentTeam->getTotalUserWithInvitationsCount() == 1;
?>

@if($teamExists && $noInvitesSent)
<div class="wittyworks-upgrade-banner" id="wittyworks-upgrade-banner">
    <div class="wittyworks-upgrade-banner-text-container">
        <div class="wittyworks-upgrade-banner-title">
            {{ __('content.invite_team_members_title') }}
        </div>
        <div class="wittyworks-upgrade-banner-text">
            {{ __('content.invite_team_members_text') }}
        </div>
    </div>
    <div class="wittyworks-upgrade-banner-button-container">
        <a class="wittyworks-button wittyworks-button--purple" href="{{ route('teams.show') }}" target="_blank" rel="noopener">
            {{ __('content.invite_team_members_button') }}
        </a>
    </div>
</div>
@endif

