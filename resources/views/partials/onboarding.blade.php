<div class="onboarding-container">
    @auth
    @php
        $user = Auth::user();  
        $currentTeam = $user->currentTeam;  
    @endphp

    @if(!$currentTeam || $user->can('update', $currentTeam))
        <div class="onboarding-title">{{ __('content.onboarding_finish_setup_title') }}</div>
        <div class="onboarding-tagline">{{ __('content.onboarding_finish_setup_tagline') }}</div>
        
        @if ($currentTeam)
        <div class="onboarding-step-container--complete">
            <div class="onboarding-step-title">{{ __('content.onboarding_create_team') }}</div>
            <div class="onboarding-step-tagline">{{ __('content.onboarding_create_team_tagline') }}</div>
        </div>
        @else
        <a href="{{ route('teams.create') }}">
            <div class="onboarding-step-container">
                <div class="onboarding-step-title">{{ __('content.onboarding_create_team') }}</div>
                <div class="onboarding-step-tagline">{{ __('content.onboarding_create_team_tagline') }}</div>
            </div>
        </a>
        @endif 

        @if (!$currentTeam)
        <div class="onboarding-step-container--deactivated">
            <div class="onboarding-step-title--deactivated">{{ __('content.onboarding_configure_organization_guidelines') }}</div>
            <div class="onboarding-step-tagline--deactivated">{{ __('content.onboarding_configure_organization_guidelines_tagline') }}</div>
        </div>    
        @elseif ($currentTeam && $currentTeam->organizationGuidelines)
        <div class="onboarding-step-container--complete">
            <div class="onboarding-step-title">{{ __('content.onboarding_configure_organization_guidelines') }}</div>
            <div class="onboarding-step-tagline">{{ __('content.onboarding_configure_organization_guidelines_tagline') }}</div>
        </div>
        @else
        <a href="{{ route('organization-guidelines', $currentTeam->id) }}">
            <div class="onboarding-step-container--complete">
                <div class="onboarding-step-title">{{ __('content.onboarding_configure_organization_guidelines') }}</div>
                <div class="onboarding-step-tagline">{{ __('content.onboarding_configure_organization_guidelines_tagline') }}</div>
            </div>
        </a>
        @endif 

        @if (!$currentTeam)
        <div class="onboarding-step-container--deactivated">
            <div class="onboarding-step-title--deactivated">{{ __('content.onboarding_invite_users') }}</div>
            <div class="onboarding-step-tagline--deactivated">{{ __('content.onboarding_invite_users_tagline') }}</div>
        </div>    
        @elseif ($currentTeam && $currentTeam->total_user_licenses_count > 1)
        <div class="onboarding-step-container--complete">
            <div class="onboarding-step-title">{{ __('content.onboarding_invite_users') }}</div>
            <div class="onboarding-step-tagline">{{ __('content.onboarding_invite_users_tagline') }}</div>
        </div>
        @else
        <a href="{{ route('teams.show', $currentTeam->id) }}">
            <div class="onboarding-step-container--complete">
                <div class="onboarding-step-title">{{ __('content.onboarding_invite_users') }}</div>
                <div class="onboarding-step-tagline">{{ __('content.onboarding_invite_users_tagline') }}</div>
            </div>
        </a>
        @endif 

    @endif

    @if($user->invitations->count())
    <div class="mt-6">
        {{ trans_choice('content.open_invitiations', $user->invitations->count()) }}
        @if($user->ownedTeams()->count())
        <div>
            {!! __('content.contact_support_to_delete_owned_teams') !!}
        </div>
        @else
        @if($user->allTeams()->count())
        <div>
            <strong>
                {{ __('content.accepting_invitation_will_result_in_leaving_your_current_team', ['team_name' => $user->allTeams()->first()->name]) }}
            </strong>
        </div>
        @endif
        <ul>
        @foreach($user->invitations as $invitation)
            <li>
                {{ $invitation->team->name }}
                <a href="{{ route('team-invitations.accept', ['invitation' => $invitation]) }}">
                    {{ __('content.accept_invitiation') }}
                </a>
                <a href="{{ route('team-invitations.reject', ['invitation' => $invitation]) }}">
                    {{ __('content.reject_invitiation') }}
                </a>
            </li>
        @endforeach
        </ul>
        @endif
    </div>
    @endif

    @endauth
</div>