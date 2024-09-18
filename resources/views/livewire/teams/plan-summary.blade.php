<x-form-section submit="updateLicenses">

    <x-slot name="title">
        <h2 id="plan-summary-title">{{ __('teams.plan_summary') }}</h2>
    </x-slot>

    <x-slot name="description"></x-slot>

    <x-slot name="form">

        <x-label class="lato-small-paragraph-title-h4" for="name">
            {{ __('teams.plan_name') }}
        </x-label>
        <p class="lato-small-text-p margin-bottom">
            @if($team->subscription() && !$team->subscription()->valid())
                {{ __('stripe.'.$team->subscription()->planId(true)) }}
            @else
                {{ __('stripe.'.($team->planId() ?? 'no_plan')) }}
            @endif
        </p>

        <!-- Subscription Details -->
        @if($team->subscription())
            @if($team->subscription()->ended())
                <p class="lato-small-text-p margin-bottom text-red-500">
                    {{ __('teams.subscription_has_ended') }} {{ $team->subscription()->ends_at->calendar() }}.
                </p>
            @elseif($team->subscription()->ends_at)
                <p class="lato-small-text-p margin-bottom text-red-500">
                    {{ __('teams.end_date') }} {{ $team->subscription()->ends_at->calendar() }}.
                </p>
            @elseif($team->subscription()->renews_at)
                <p class="lato-small-text-p margin-bottom text-red-500">
                    {{ __('teams.renewal_date') }} {{ $team->subscription()->renews_at->calendar() }}.
                </p>
            @endif
        @elseif($team->hasExpiredGenericTrial())
            <p class="lato-small-text-p margin-bottom text-red-500">
                {{ __('teams.trial_has_ended') }} {{ $team->trial_ends_at->calendar() }}.
            </p>
        @elseif($team->onGenericTrial())
            <p class="lato-small-text-p margin-bottom">
                {{ __('teams.trail_end_date') }} {{ $team->trial_ends_at->calendar() }}.
            </p>
        @endif

        <!-- Team Owner -->
        <x-label class="lato-small-paragraph-title-h4 mt-4" for="owner">
            {{ __('teams.team_owner') }}
        </x-label>
        <p class="lato-small-text-p margin-bottom">
            {{ $team->owner->name }}
            (<a href="mailto:{{ $team->owner->email }}" aria-label="Email {{ $team->owner->name }}">
                {{ $team->owner->email }}
            </a>)
        </p>

        @if($team->subscribed() || $team->onGenericTrial() || $team->hasExpiredSubscription())
        <h3 class="lato-small-paragraph-title-h4 mt-4" id="what-is-included">
            {{ __('teams.what_is_included') }}
        </h3>
        <ul aria-labelledby="what-is-included">
            <li>
                {{ __('teams.total_of_max_used_licenses', ['total' => $team->userLicenses()->count(), 'max_count' => $team->getUserLicensesCount(true)]) }}
                @if($team->subscription() && $team->subscription()->isPaidByInvoice())
                    <div>
                        {!! __('teams.more_licenses') !!}
                    </div>
                @endif
            </li>
            <li>
                {{ trans_choice('teams.total_of_max_used_dictionary', $team->getTermReplacementsCount(), ['total' => $team->getTotalTermReplacementsCount(), 'max_count' => $team->getTermReplacementsCount()]) }}
            </li>
            <li>
                {{ trans_choice('teams.total_of_max_used_ignored', $team->getFalsePositivesCount(), ['total' => $team->getTotalFalsePositivesCount(), 'max_count' => $team->getFalsePositivesCount()]) }}
            </li>
        </ul>
        @endif

        @if($team->subscribed() && Auth::user()->ownsTeam($team))
            <h3 class="lato-small-paragraph-title-h4 mt-4 mb-4">
                {{ __('teams.update_subscription') }}
            </h3>

            <a href="{{ route('stripe.portal') }}" role="button">
                {{ $team->subscription()->isPaidByInvoice() ? __('stripe.contact_sales') : __('stripe.billing') }}
            </a>

            @if(!$team->subscription()->isPaidByInvoice())
            <p class="lato-small-text-p margin-bottom">
                {!! __('teams.billing_portal_features') !!}
            </p>
            @endif
        @endif

        @if(!$team->subscription() || !$team->subscription()->isPaidByInvoice())
            <h3 class="lato-small-paragraph-title-h4 mt-4">
                {{ __('teams.license_count_label') }}
            </h3>

        @if(Auth::user()->ownsTeam($team))
            <div class="wittyworks-license-dropdown-container margin-bottom">
                <label for="license_count" aria-label="{{ __('teams.select_license_count_aria_label') }}">
                    <x-select id="license_count"
                        :options="$licenseOptions"
                        wire:model="licenseCount"
                        class="wittyworks-margin-right"
                    />
                </label>

                <x-input-error for="license_count" class="ml-2" />
            </div>
        @else
            <p class="lato-small-text-p margin-bottom red">
                {{ __('teams.to_upgrade_contact_owner') }}
            </p>
        @endif
        @endif
    </x-slot>

    @if(Auth::user()->ownsTeam($team) && (!$team->subscription() || !$team->subscription()->isPaidByInvoice()))
        <x-slot name="actions">
            @if($team->subscribed())
            @include('partials/save_cancel_action')
            @else
            <div class="flex flex-row align-middle items-center" role="toolbar" aria-label="Action buttons">
                <x-button>
                    {{ __('content.subscribe') }}
                </x-button>
            </div>
        @endif
        </x-slot>
    @endif
</x-form-section>
