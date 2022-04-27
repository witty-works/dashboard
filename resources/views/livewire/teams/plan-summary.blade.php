<x-jet-section-border />
<x-jet-form-section  submit="">
    <x-slot name="title">
        {{ __('teams.plan_summary') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('teams.plan_summary_description')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('teams.plan_name') }}" />
            @if($team->subscribed())
                {{ $team->subscription()->planName() }}
            @else
                {{ __('stripe.witty_me') }}

            @if(Auth::user()->ownsTeam($team))
            <a href="{{ route('stripe.portal') }}">
                {{ __('teams.upgrade') }}
            </a>
            @endif
            @endif
        </div>

        <div class="mt-5">
            <x-jet-label for="name" value="{{ __('teams.team_owner') }}" />
                {{ $team->owner->name }} (<a href="mailto:{{ $team->owner->email }}">{{ $team->owner->email }}</a>)
        </div>
        <div class="mt-5">
            <div>
                {{ __('teams.what_is_included') }}
            </div>
            <div class="mt-5">
                <x-jet-label for="name" value="{{ __('teams.user_licenses') }}" />
                {{ __('teams.total_of_max_used', ['total' => $team->getTotalUserCount(), 'max_count' => $team->getUserLicensesCount()]) }}
                @if($team->subscribed() && $team->subscription()->isPaidByInvoice())
                    <div>
                        {!! __('teams.more_licenses') !!}
                    </div>
                @elseif ($team->getTotalUserCount() != $team->getUserLicensesCount())
                    <div>
                        @if($team->subscribed())
                        @if($team->getTotalUserCount() > $team->getUserLicensesCount())
                        {{ trans_choice('teams.user_licenses_count_will_be_increased_updated_at', $team->getTotalUserCount() - $team->getUserLicensesCount(), ['diff' => $team->getTotalUserCount() - $team->getUserLicensesCount(), 'in' => $team->subscription()->update_user_licenses_at->diffForHumans()]) }}
                        @else
                        {{ trans_choice('teams.user_licenses_count_will_be_decreased_updated_at', $team->getUserLicensesCount() - $team->getTotalUserCount(), ['diff' => $team->getUserLicensesCount() - $team->getTotalUserCount(), 'in' => $team->subscription()->update_user_licenses_at->diffForHumans()]) }}
                        @endif
                        @elseif(Auth::user()->ownsTeam($team) && $team->getTotalUserCount() > $team->getUserLicensesCount())
                        {!! trans_choice('teams.please_remove_users_or_upgrade', $team->getTotalUserCount() - $team->getUserLicensesCount(), ['diff' => $team->getTotalUserCount() - $team->getUserLicensesCount(), 'url' => route('stripe.portal')]) !!}
                        @endif
                    </div>
                @endif
            </div>

            <div class="mt-5">
                <x-jet-label for="name" value="{{ __('teams.term_replacements') }}" />
                    {{ __('teams.total_of_max_used', ['total' => $team->getTotalTermReplacementsCount(), 'max_count' => $team->getTermReplacementsCount()]) }}
            </div>

            <div class="mt-5">
                <x-jet-label for="name" value="{{ __('teams.false_positives') }}" />
                    {{ __('teams.total_of_max_used', ['total' => $team->getTotalFalsePositivesCount(), 'max_count' => $team->getFalsePositivesCount()]) }}
            </div>
        </div>

        @if($team->subscribed())
        @if($team->subscription()->ends_at)
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('teams.end_date') }}" />
            {{ $team->subscription()->ends_at->toFormattedDateString() }}
        </div>
        @elseif($team->subscription()->renews_at)
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('teams.renewal_date') }}" />
            {{ $team->subscription()->renews_at->toFormattedDateString() }}
        </div>
        @endif
        @endif    
     </x-slot>
</x-jet-form-section>