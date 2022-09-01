@php
$user = Auth::user();
@endphp
 
@if($user->invitations->count())
    <div class="wittyworks-upgrade-banner">
        <div class="wittyworks-upgrade-banner-text-container">
            <div class="wittyworks-upgrade-banner-text">
                @if($user->ownedTeams()->count() && $user->ownedTeams()->first()->hasLanguageRules())
                    <div>
                        {!! __('content.contact_support_to_delete_owned_teams', ['deleteUrl' => route('teams.show') . '#delete-team']) !!}
                    </div>
                    <br />
                @endif

                <div>
                    {!! __('content.accepting_invitation_will_result_in_leaving_your_current_team', ['team_name' => $user->allTeams()->first()->name, 'invite_team_name' => $user->invitations[0]->team->name, 'invite_user_email' => $user->invitations[0]->team->owner->email]) !!}
                </div>
        </div>
        </div>
        <div>
            <div class="wittyworks-accept-invite-wrapper">
                <div class="wittyworks-upgrade-banner-button-container">
                    <a class="wittyworks-button wittyworks-button--purple" href="{{ route('team-invitations.accept', ['invitation' => $user->invitations[0]]) }}">
                        {{ __('content.accept_invitiation') }}
                    </a>
                </div>
                <div class="wittyworks-upgrade-banner-button-container">
                    <a class="wittyworks-button wittyworks-button--white-purple" href="{{ route('team-invitations.reject', ['invitation' => $user->invitations[0]]) }}">
                        {{ __('content.reject_invitiation') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
 @endif