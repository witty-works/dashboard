@php
$invitation = $user->invitations->first();
@endphp
<div class="wittyworks-upgrade-banner">
    <div class="lato-paragraph-text-p-purple max-width-80">
        {!! __('content.accept_invitation_description', ['invite_team_name' => $user->invitations[0]->team->name, 'invite_user_email' => $user->invitations[0]->team->owner->email]) !!}
    </div>
    <div class="wittyworks-accept-invite-wrapper">
        <div class="wittyworks-upgrade-banner-button-container">
            <a class="button primary-button-purple" href="{{ route('team-invitations.accept', ['invitation' => $invitation]) }}">
                {{ __('content.accept_invitiation') }}
            </a>
        </div>
        <div class="wittyworks-upgrade-banner-button-container">
            <a class="button secondary-button-purple" href="{{ route('team-invitations.reject', ['invitation' => $invitation]) }}">
                {{ __('content.reject_invitiation') }}
            </a>
        </div>
    </div>
</div>