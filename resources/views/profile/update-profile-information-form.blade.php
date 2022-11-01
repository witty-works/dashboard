<x-jet-form-section submit="updateProfileInformation">
        <x-slot name="title">
            {{ __('content.manage_account') }}
        </x-slot>

        <x-slot name="description"></x-slot>

        <x-slot name="form">  
        <div class="wittyworks-form-section-wrapper">
            <div class="lato-small-paragraph-title-h4">{{ __('teams.profile') }}</div>

            <x-jet-label for="name" value="{{ __('content.name') }}" />
            <div class="container-row lato-small-text-p margin-bottom">
                {{ $state['name'] }}&nbsp;
                {!! __('content.update_your_account_profile', ['profile_url' => route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'profile'])]) !!}
            </div>

            <x-jet-label for="email" value="{{ __('content.email') }}"/>
            <div class="container-row lato-small-text-p">
                {{ $state['email'] }}
            </div>

            @if(!Auth::user()->has_consented_to_mailing)
            <x-jet-label for="mailing" value="{{ __('content.mailing_consent_title') }}"/>
            <div class="container-row lato-small-text-p">
                {{ __('content.mailing_consent_text') }}

                <a class="button primary-button-purple" href="{{ route('user.mailing_consent') }}?consent=1">
                    {{ __('content.mailing_consent_button') }}
                </a>
            </div>
            @endif
        </div>

        @php
            $user = Auth::user();
            $team = $user->currentTeam;
        @endphp

        <div class="lato-small-paragraph-title-h4">{{ __('teams.team_plan_headline') }}</div>

        <x-jet-label for="name" value="{{ __('teams.plan_name') }}" class="lato-paragraph-text-p" />
        <div class="lato-small-text-p margin-bottom">
            {{ $user->subscribed() ? $user->subscription()->planName() : __('stripe.witty_free') }}

            @if(!Auth::user()->ownsTeam($team))
                {{ __('teams.to_upgrade_contact_owner') }}
            @endif
        </div>

        @if($team)
            <x-jet-label for="name" value="{{ __('teams.team_owner') }}"/>
            <div class="lato-small-text-p margin-bottom">
                {{ $team->owner->name }} (<a href="mailto:{{ $team->owner->email }}">{{ $team->owner->email }}</a>)
            </div>
        @endif
            <x-jet-label for="name" value="{{ __('teams.plan_name') }}" />
            <div class="lato-small-text-p margin-bottom">
                {{ $user->subscribed() ? $user->subscription()->planName() : __('stripe.witty_free') }}
            </div>

            <x-jet-label for="name" value="{{ __('teams.term_replacements') }}"/>
            <div class="lato-small-text-p margin-bottom">
                {{ __('teams.total_of_max_used_dictionary', ['total' => $user->getTotalTermReplacementsCount(), 'max_count' => $user->getTermReplacementsCount()]) }}
            </div>

            <x-jet-label for="name" value="{{ __('teams.false_positives') }}"/>
            <div class="lato-small-text-p">
                {{ __('teams.total_of_max_used_ignored', ['total' => $user->getTotalFalsePositivesCount(), 'max_count' => $user->getFalsePositivesCount()]) }}
            </div>
    </x-slot>
</x-jet-form-section>
