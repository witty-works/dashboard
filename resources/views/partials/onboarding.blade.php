@auth
@php
$user = Auth::user();
$currentTeam = $user->currentTeam;

if(!$currentTeam || $user->can('update', $currentTeam)) {
    $showOnboardingSteps = false;

    $onboardingSteps = [
        'createTeam' => [
                'title' =>  __('content.onboarding_create_team'),
                'tagline' => __('content.onboarding_create_team_tagline'),
                'state' => 'deactivated',
                'link' => false,
            ],
        'organizationGuidelines' =>
            [
                'title' => __('content.onboarding_configure_organization_guidelines'),
                'tagline' => __('content.onboarding_configure_organization_guidelines_tagline'),
                'state' => 'deactivated',
                'link' => false,
            ],
        'inviteUsers' =>
            [
                'title' => __('content.onboarding_invite_users'),
                'tagline' => __('content.onboarding_invite_users_tagline'),
                'state' => 'deactivated',
                'link' => false,
            ], 
    ];

    if ($currentTeam) {
        $onboardingSteps['createTeam']['state'] = 'complete';
    } else {
        $showOnboardingSteps = true;
        $onboardingSteps['createTeam']['state'] = 'todo';
        $onboardingSteps['createTeam']['link'] = route('teams.create');
    }

    if (!$currentTeam) {
        $onboardingSteps['organizationGuidelines']['state'] = 'deactivated';
    } else if ($currentTeam && $currentTeam->organizationGuidelines) {
        $onboardingSteps['organizationGuidelines']['state'] = 'complete';
    } else {
        $showOnboardingSteps = true;
        $onboardingSteps['organizationGuidelines']['state'] = 'todo';
        $onboardingSteps['organizationGuidelines']['link'] = route('organization-guidelines', $currentTeam->id);
    }

    if (!$currentTeam || !$currentTeam->organizationGuidelines) {
        $onboardingSteps['inviteUsers']['state'] = 'deactivated';
    } else if ($currentTeam && $currentTeam->getTotalUserLicensesCount() > 1) {
        $onboardingSteps['inviteUsers']['state'] = 'complete';
    } else {
        $showOnboardingSteps = true;
        $onboardingSteps['inviteUsers']['state'] = 'todo';
        $onboardingSteps['inviteUsers']['link'] = route('teams.show', $currentTeam->id);
    }
}
@endphp

@if($showOnboardingSteps)
    <div class="onboarding-container">
        <div class="onboarding-title">{{ __('content.onboarding_finish_setup_title') }}</div>
        <div class="onboarding-tagline">{{ __('content.onboarding_finish_setup_tagline') }}</div>

        <div class="onboarding-steps">
            @foreach($onboardingSteps as $step)
            <div class="onboarding-step-container onboarding-step-{{ $step['state'] }}">
                @if ($step['link']) <a href="{{ $step['link'] }}"> @endif
                    <div class="onboarding-step-text-wrapper">
                        <div class="onboarding-step-title--{{ $step['state'] }}">{{ $step['title'] }}</div>
                        <div class="onboarding-step-tagline--{{ $step['state'] }}">{{ $step['tagline'] }}</div>
                    </div>
                @if ($step['link']) </a> @endif
                @if ($step['state'] == 'complete')
                <img class="onboarding-step-icon" src="{{ url('svg/check-mark.svg') }}" alt="checkmark" />
                @else 
                <img class="onboarding-step-icon" src="{{ url('svg/arrow-right.svg') }}" alt="arrow"/>
                @endif
            </div>
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
</div>
@endauth

<div class="onboarding-container--two-col">
    <div class="onboarding-container-col-one">
        <div class="onboarding-title">{{ __('content.onboarding_quickLinks') }}</div>
        <div class="onboarding-quick-links-container">
            @if($currentTeam)
            <a class="onboarding-iconWrapper" href="{{ route('teams.show', $currentTeam->id) }}">
                <img src="{{ url('svg/team-setup.svg') }}" alt="{{ __('content.onboarding_team_setup') }}"/>
                <div class="onboarding-icon-description">{{ __('content.onboarding_team_setup') }}</div>
            </a>
            <a class="onboarding-iconWrapper" href="{{ route('language-guidelines', $currentTeam->id) }}">
                <img src="{{ url('svg/language-guidelines.svg') }}" alt="{{ __('content.onboarding_language_guidelines') }}"/>
                <div class="onboarding-icon-description">{{ __('content.onboarding_language_guidelines') }}</div>
            </a>
            <a class="onboarding-iconWrapper" href="{{ route('stripe.portal') }}">
                <img src="{{ url('svg/payment-billing.svg') }}" alt="{{ __('content.onboarding_payment') }}"/>
                <div class="onboarding-icon-description">{{ __('content.onboarding_payment') }}</div>
            </a>
            @endif
            <a class="onboarding-iconWrapper" href="https://www.witty.works/help">
                <img src="{{ url('svg/support.svg') }}" alt="{{ __('content.onboarding_support') }}"/>
                <div class="onboarding-icon-description">{{ __('content.onboarding_support') }}</div>
            </a>
        </div>
    </div>
    <div class="onboarding-container-col-two">
        <div class="onboarding-title">{{ __('content.onboarding_team_stats') }}</div>
        <img class="onboarding-analytics-img" src="{{ url('svg/analytics-coming-soon.svg') }}" alt="{{ __('content.onboarding_team_stats') }}"/>
    </div>
</div>

