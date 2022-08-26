<x-jet-form-section submit="updateProfileInformation">
        <x-slot name="title">
            {{ __('content.subscription') }}
        </x-slot>

        <x-slot name="description"></x-slot>

        <x-slot name="form">  
        <div class="wittyworks-form-section-wrapper">
        <div class="wittyworks-subscription-headline">{{ __('teams.profile') }}</div>
            <!-- Name -->
            <div class="wittyworks-form-section">
                <x-jet-label for="name" value="{{ __('content.name') }}" />
                <div class="wittyworks-update-account">
                    {{ $state['name'] }}&nbsp;
                    {!! __('content.update_your_account_profile', ['profile_url' => route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'profile'])]) !!}
                </div>

            </div>

            <!-- Email -->
            <div class="wittyworks-form-section">
                <x-jet-label for="email" value="{{ __('content.email') }}" />
                <div class="wittyworks-update-account">
                    {{ $state['email'] }}
                </div>
            </div>
        </div>

        @php
            $user = Auth::user();
            $team = $user->currentTeam;
        @endphp

        <div class="wittyworks-subscription-headline">{{ __('teams.plan_headline') }}</div>

        <div class="wittyworks-form-section">
            <x-jet-label for="name" value="{{ __('teams.team_owner') }}" />
                {{ $team->owner->name }} (<a href="mailto:{{ $team->owner->email }}">{{ $team->owner->email }}</a>)
        </div>

        <div class="wittyworks-form-section">
        <div class="wittyworks-update-account">
            <x-jet-label for="name" value="{{ __('teams.plan_name') }}" />
        </div>
            <div>
                {{ $user->subscribed() ? $user->subscription()->planName() : __('stripe.witty_free') }}
            </div>

            @if($team && $user->ownsTeam($team))
                <a href="{{ route('stripe.portal') }}">
                    @if(!$team->subscribed())
                    <div class="wittyworks-upgrade-banner">
                        <div class="wittyworks-upgrade-banner-text-container">
                            <div class="wittyworks-upgrade-banner-title">
                            {{ __('content.onboarding_install_witty_title') }}
                            </div>
                            <div class="wittyworks-upgrade-banner-text">
                            {{ __('content.onboarding_install_witty_text') }}
                            </div>
                        </div>
                        <div class="wittyworks-upgrade-banner-button-container">
                            <a class="wittyworks-upgrade-banner-button" href="https://www.witty.works/select-browser" target="_blank" rel="noopener">
                                {{ __('content.onboarding_install_witty_button') }}
                            </a>
                        </div>
                    </div>
                    @endif
                </a>
            @endif
        </div>

        <!-- TODO: add how many team members have been added + button (either add, or upgrade) -->

        <div class="wittyworks-form-section">
            <x-jet-label for="name" value="{{ __('teams.term_replacements') }}" />
                {{ __('teams.total_of_max_used', ['total' => $user->getTotalTermReplacementsCount(), 'max_count' => $user->getTermReplacementsCount()]) }}
        </div>

        <div class="wittyworks-form-section">
            <x-jet-label for="name" value="{{ __('teams.false_positives') }}" />
                {{ __('teams.total_of_max_used', ['total' => $user->getTotalFalsePositivesCount(), 'max_count' => $user->getFalsePositivesCount()]) }}
        </div>
    </x-slot>
</x-jet-form-section>
