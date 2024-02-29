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
            {{ $team->subscribed() ? $team->subscription()->planName() : __('stripe.witty_free') }}
        </p>

        <!-- Subscription Details -->
        @if($team->subscribed())
            @if($team->subscription()->ends_at)
                <p class="lato-small-text-p margin-bottom">
                    {{ __('teams.end_date') }} {{ $team->subscription()->ends_at->toFormattedDateString() }}.
                </p>
            @elseif($team->subscription()->renews_at)
                <p class="lato-small-text-p margin-bottom red">
                    {{ __('teams.renewal_date') }} {{ $team->subscription()->renews_at->toFormattedDateString() }}.
                </p>
            @endif
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

        <h3 class="lato-small-paragraph-title-h4 mt-4" id="what-is-included">
            {{ __('teams.what_is_included') }}
        </h3>
        <ul aria-labelledby="what-is-included">
            <li>
                {{ __('teams.total_of_max_used_licenses', ['total' => $team->getTotalUserWithInvitationsCount(), 'max_count' => $team->getUserLicensesCount()]) }}
                @if($team->subscribed() && $team->subscription()->isPaidByInvoice())
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

        <h3 class="lato-small-paragraph-title-h4 mt-4">
            {{ __('teams.license_count_label') }}
        </h3>

        @if(!Auth::user()->ownsTeam($team))  
            <p class="lato-small-text-p margin-bottom red">
                {{ __('teams.to_upgrade_contact_owner') }}
            </p>
        @endif

        @if(Auth::user()->ownsTeam($team))
            @if($team->subscribed())
                <p class="lato-small-text-p margin-bottom">
                    <a class="button primary-button-purple" href="{{ route('stripe.portal') }}" role="button">
                        {{ $team->subscription()->isPaidByInvoice() ? __('stripe.contact_sales') : __('stripe.billing') }}
                    </a>
                </p>
            @endif

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
        @endif
    </x-slot>

    @if(Auth::user()->ownsTeam($team))
        <x-slot name="actions">
            @include('partials/save_cancel_action')
        </x-slot>
    @endif
</x-form-section>
