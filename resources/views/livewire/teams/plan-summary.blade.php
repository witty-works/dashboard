<x-jet-form-section submit="updateLicenses">
    <x-slot name="title">
        {{ __('teams.plan_summary') }}
    </x-slot>

    <x-slot name="description"></x-slot>

    <x-slot name="form">
            <x-jet-label class="wittyworks-subscription-headline" for="name" value="{{ __('teams.plan_name') }}" />            
            <div>
                {{ $team->subscribed() ? $team->subscription()->planName() : __('stripe.witty_free') }}
            </div>

            @if(Auth::user()->ownsTeam($team))
            @if($team->subscribed())
            <div class="wittyworks-upgrade-button-container">
                <a class="wittyworks-button wittyworks-button--purple" href="{{ route('stripe.portal') }}">
                    {{ $team->subscription()->isPaidByInvoice() ? __('stripe.contact_sales') : __('stripe.billing') }}
                </a>
            </div>
            @endif

            
            <x-jet-label class="wittyworks-margin-top" for="license_count" value="{{ __('teams.license_count_label') }}" />
            
            <div class="wittyworks-license-dropdown-container">
                <x-select id="license_count"
                    :options="$licenseOptions"
                    wire:model.defer="licenseCount"
                    class="wittyworks-margin-right"
                />

                <x-jet-button>
                    {{ $team->subscribed() ? ($team->subscription()->canceled() ? __('content.renew') : __('content.save')) : __('teams.upgrade') }}
                </x-jet-button>

                <x-jet-action-message class="inline-block" on="saved">
                    {{ __('content.saved') }}
                </x-jet-action-message>

                <x-jet-input-error for="license_count" class="ml-2" />
            </div>
               
        @endif

        <div class="mt-5 col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('teams.team_owner') }}" />
            {{ $team->owner->name }} (<a href="mailto:{{ $team->owner->email }}">{{ $team->owner->email }}</a>)
            @if($team->subscribed())
            @if($team->subscription()->ends_at)
            <div class="mt-5 col-span-6 sm:col-span-4">
                <x-jet-label for="name" value="{{ __('teams.end_date') }}" />
                {{ $team->subscription()->ends_at->toFormattedDateString() }}
            </div>
            @elseif($team->subscription()->renews_at)
            <div class="mt-5 col-span-6 sm:col-span-4">
                <x-jet-label for="name" value="{{ __('teams.renewal_date') }}" />
                {{ $team->subscription()->renews_at->toFormattedDateString() }}
            </div>
            @endif
            @endif
        </div>

        <div class="mt-5 col-span-6 sm:col-span-4 wittyworks-subscription-section">
            <div class="wittyworks-subscription-headline">
                {{ __('teams.what_is_included') }}
            </div>
            <div class="mt-5">
                <x-jet-label for="name" value="{{ __('teams.user_licenses') }}" />
                {{ __('teams.total_of_max_used_licenses', ['total' => $team->getTotalUserWithInvitationsCount(), 'max_count' => $team->getUserLicensesCount()]) }}
                @if($team->subscribed() && $team->subscription()->isPaidByInvoice())
                    <div>
                        {!! __('teams.more_licenses') !!}
                    </div>
                @endif
            </div>

            <div class="mt-5 col-span-6 sm:col-span-4">
                <x-jet-label for="name" value="{{ __('teams.term_replacements') }}" />
                    {{ __('teams.total_of_max_used_dictionary', ['total' => $team->getTotalTermReplacementsCount(), 'max_count' => $team->getTermReplacementsCount()]) }}
            </div>

            <div class="mt-5 col-span-6 sm:col-span-4">
                <x-jet-label for="name" value="{{ __('teams.false_positives') }}" />
                    {{ __('teams.total_of_max_used_ignored', ['total' => $team->getTotalFalsePositivesCount(), 'max_count' => $team->getFalsePositivesCount()]) }}
            </div>
        </div>
     </x-slot>
</x-jet-form-section>