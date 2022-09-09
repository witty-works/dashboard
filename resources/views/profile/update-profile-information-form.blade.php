<x-jet-form-section submit="updateProfileInformation">
        <x-slot name="title">
            {{ __('content.manage_account') }}
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

        <div class="wittyworks-subscription-headline">{{ __('teams.team_plan_headline') }}</div>

        @if($team)
        <div class="wittyworks-form-section">
            <x-jet-label for="name" value="{{ __('teams.team_owner') }}" />
                {{ $team->owner->name }} (<a href="mailto:{{ $team->owner->email }}">{{ $team->owner->email }}</a>)
        </div>
        @endif

        <div class="wittyworks-form-section">
        <div class="wittyworks-update-account">
            <x-jet-label for="name" value="{{ __('teams.plan_name') }}" />
        </div>
            <div>
                {{ $user->subscribed() ? $user->subscription()->planName() : __('stripe.witty_free') }}
            </div>
        </div>

        <!-- TODO: add how many team members have been added + button (either add, or upgrade) -->

        <div class="wittyworks-form-section">
            <x-jet-label for="name" value="{{ __('teams.term_replacements') }}" />
                {{ __('teams.total_of_max_used_dictionary', ['total' => $user->getTotalTermReplacementsCount(), 'max_count' => $user->getTermReplacementsCount()]) }}
        </div>

        <div class="wittyworks-form-section">
            <x-jet-label for="name" value="{{ __('teams.false_positives') }}" />
                {{ __('teams.total_of_max_used_ignored', ['total' => $user->getTotalFalsePositivesCount(), 'max_count' => $user->getFalsePositivesCount()]) }}
        </div>
    </x-slot>
</x-jet-form-section>
