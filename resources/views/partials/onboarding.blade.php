@php
$user = Auth::user();
@endphp
 
@if($user->invitations->count())
    <div class="wittyworks-upgrade-banner">
        <div class="wittyworks-upgrade-banner-text-container">
            <div class="wittyworks-upgrade-banner-title">
                {{ __('content.onboarding_invitations') }}
            </div>
            <div class="wittyworks-upgrade-banner-text">
                <div>{{ trans_choice('content.open_invitiations', $user->invitations->count()) }}</div>
                @if($user->ownedTeams()->count() && $user->ownedTeams()->first()->hasLanguageRules())
                    <div>{!! __('content.contact_support_to_delete_owned_teams', ['deleteUrl' => route('teams.show') . '#delete-team']) !!}</div>
                @elseif($user->allTeams()->count())
                    <div>
                        <strong>{{ __('content.accepting_invitation_will_result_in_leaving_your_current_team', ['team_name' => $user->allTeams()->first()->name]) }}</strong>
                    </div>
                @endif
            </div>
        </div>
        <div class="wittyworks-accept-invite-wrapper">
            <div class="wittyworks-accept-invite-team-name">
                {{ $user->invitations[0]->team->name }} - <a href="mailto:{{ $user->invitations[0]->team->owner->email }}">{{ $user->invitations[0]->team->owner->email }}</a>
            </div>
            <div class="onboarding-step-tagline--todo">
                <div class="wittyworks-upgrade-banner-button-container">
                    <a class="wittyworks-accept-invite-button" href="{{ route('team-invitations.accept', ['invitation' => $user->invitations[0]]) }}">
                        {{ __('content.accept_invitiation') }}
                    </a>
                </div>
                <div class="wittyworks-upgrade-banner-button-container">
                    <a class="wittyworks-accept-invite-button" href="{{ route('team-invitations.reject', ['invitation' => $user->invitations[0]]) }}">
                        {{ __('content.reject_invitiation') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
 @endif