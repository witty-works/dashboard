<div class="wittyworks-upgrade-banner">
    <div class="lato-paragraph-text-p-purple max-width-80">
        {!! __('content.accepting_invitation_will_result_in_leaving_your_current_team', ['team_name' => $user->currentTeam->name, 'invite_team_name' => $user->invitations[0]->team->name, 'invite_user_email' => $user->invitations[0]->team->owner->email]) !!}
        @if($user->ownsTeam($user->currentTeam) && $user->currentTeam->subscribed())
        <br />
        @if($user->invitations[0]->team->subscribed())
        {!! __('content.accepting_invitation_will_cancel') !!}
        @else
        {!! __('content.accepting_invitation_will_cancel_and_downgrade') !!}
        @endif
        @endif
        </div>
    <div class="wittyworks-accept-invite-wrapper">
        <div class="wittyworks-upgrade-banner-button-container">
            <a class="button primary-button-purple" href="{{ route('team-invitations.accept', ['invitation' => $user->invitations[0]]) }}">
                {{ __('content.accept_invitiation') }}
            </a>
        </div>
        <div class="wittyworks-upgrade-banner-button-container">
            <a class="button secondary-button-purple" href="{{ route('team-invitations.reject', ['invitation' => $user->invitations[0]]) }}">
                {{ __('content.reject_invitiation') }}
            </a>
        </div>
    </div>
</div>