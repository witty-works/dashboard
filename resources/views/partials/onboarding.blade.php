<div class="onboarding-container">
    @auth
    @php
        $user = Auth::user();  
        $currentTeam = $user->currentTeam;

        $onboardingSteps = [
            'createTeam' => [
                    'title' =>  'content.onboarding_create_team',
                    'tagline' => 'content.onboarding_create_team_tagline',
                    'state' => 'deactivated',
                    'link' => false,
                ],
            'organizationGuidelines' =>
                [
                    'title' => 'content.onboarding_configure_organization_guidelines',
                    'tagline' => 'content.onboarding_configure_organization_guidelines_tagline',
                    'state' => 'deactivated',
                    'link' => false,
                ],
            'inviteUsers' =>
                [
                    'title' => 'content.onboarding_invite_users',
                    'tagline' => 'content.onboarding_invite_users_tagline',
                    'state' => 'deactivated',
                    'link' => false,
                ], 
        ];
    @endphp

    @if(!$currentTeam || $user->can('update', $currentTeam))
        <div class="onboarding-title">{{ __('content.onboarding_finish_setup_title') }}</div>
        <div class="onboarding-tagline">{{ __('content.onboarding_finish_setup_tagline') }}</div>
        
        @php
        if ($currentTeam) {
            $onboardingSteps['createTeam']['state'] = 'complete';
        } else {
            $onboardingSteps['createTeam']['state'] = 'todo';
            $onboardingSteps['createTeam']['link'] = route('teams.create');
        }

        if (!$currentTeam) {
            $onboardingSteps['organizationGuidelines']['state'] = 'deactivated';
        } else if ($currentTeam && $currentTeam->organizationGuidelines) {
            $onboardingSteps['organizationGuidelines']['state'] = 'complete';
        } else {
            $onboardingSteps['organizationGuidelines']['state'] = 'todo';
            $onboardingSteps['organizationGuidelines']['link'] = route('organization-guidelines', $currentTeam->id);
        }

        if (!$currentTeam->organizationGuidelines) {
            $onboardingSteps['inviteUsers']['state'] = 'deactivated';
        } else if ($currentTeam && $currentTeam->total_user_licenses_count > 1) {
            $onboardingSteps['inviteUsers']['state'] = 'complete';
        } else {
            $onboardingSteps['inviteUsers']['state'] = 'todo';
            $onboardingSteps['inviteUsers']['link'] = route('teams.show', $currentTeam->id);
        }
        @endphp

        <div class="onboarding-steps">
            @foreach($onboardingSteps as $step)
            @if ($step['link']) <a href="{{ $step['link'] }}"> @endif
            <div class="onboarding-step-container--{{ $step['state'] }}">
                <div class="onboarding-step-text-wrapper">
                    <div class="onboarding-step-title--{{ $step['state'] }}">{{ __($step['title']) }}</div>
                    <div class="onboarding-step-tagline--{{ $step['state'] }}">{{ __($step['tagline']) }}</div>
                </div>
                <div class="onboarding-step-icon-wrapper">
                    @if ($step['state'] == 'complete')
                    <img src="{{ url('svg/check-mark.svg') }}"/>
                    @else 
                    <img src="{{ url('svg/arrow-right.svg') }}"/>
                    @endif
                </div>
            </div>
            @if ($step['link']) </a> @endif
            @endforeach
        </div>
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