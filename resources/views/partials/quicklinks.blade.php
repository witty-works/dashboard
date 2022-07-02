@php
$user = Auth::user();
if ($user) {
    $currentTeam = $user->currentTeam;
}
@endphp
<div class="onboarding-container--two-col">
    <div class="onboarding-container-col-one">
        <div class="onboarding-title">{{ __('content.onboarding_quickLinks') }}</div>
        <div class="onboarding-quick-links-container">
            @if($currentTeam)
            <a class="onboarding-iconWrapper" href="{{ route('teams.show') }}">
                <img src="{{ url('svg/team-setup.svg') }}" alt="{{ __('content.onboarding_team_setup') }}"/>
                <div class="onboarding-icon-description">{{ __('content.onboarding_team_setup') }}</div>
            </a>
            <a class="onboarding-iconWrapper" href="{{ route('teams.language-guidelines') }}">
                <img src="{{ url('svg/language-guidelines.svg') }}" alt="{{ __('content.onboarding_language_guidelines') }}"/>
                <div class="onboarding-icon-description">{{ __('content.onboarding_language_guidelines') }}</div>
            </a>
            @if(Auth::user()->ownsTeam($currentTeam))
            <a class="onboarding-iconWrapper" href="{{ route('stripe.portal') }}">
                <img src="{{ url('svg/payment-billing.svg') }}" alt="{{ __('content.onboarding_payment') }}"/>
                <div class="onboarding-icon-description">{{ __('content.onboarding_payment') }}</div>
            </a>
            @endif
            @endif
            <a class="onboarding-iconWrapper" href="https://www.witty.works/en/help/wittys-help-center">
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