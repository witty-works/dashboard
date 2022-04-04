<div>
    <x-jet-section-border />

    <div class="mt-10 sm:mt-0">
        <x-section submit="">
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

                    <a href="{{ route('stripe.portal') }}">
                        {{ __('teams.upgrade') }}
                    </a>
                    @endif
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <div>
                        {{ __('teams.what_is_included') }}
                    </div>

                    <div class="mt-5">
                        <x-jet-label for="name" value="{{ __('teams.user_licenses') }}" />

                        {{ __('teams.total_of_max_used', ['total' => $team->total_user_licenses_count, 'max_count' => $team->user_licenses_count]) }}
                        @if($team->subscription()->isPaidByInvoice())
                        <div>
                            {!! __('teams.more_licenses') !!}
                        </div>
                        @elseif ($team->total_user_licenses_count != $team->user_licenses_count)
                        <div>
                            @if($team->subscribed())
                            @if($team->total_user_licenses_count > $team->user_licenses_count)
                            {{ trans_choice('teams.user_licenses_count_will_be_increased_updated_at', $team->total_user_licenses_count - $team->user_licenses_count, ['diff' => $team->total_user_licenses_count - $team->user_licenses_count, 'in' => $team->subscription()->update_user_licenses_at->diffForHumans()]) }}
                            @else
                            {{ trans_choice('teams.user_licenses_count_will_be_decreased_updated_at', $team->user_licenses_count - $team->total_user_licenses_count, ['diff' => $team->user_licenses_count - $team->total_user_licenses_count, 'in' => $team->subscription()->update_user_licenses_at->diffForHumans()]) }}
                            @endif
                            @else
                            {!! trans_choice('teams.please_remove_users_or_upgrade', $team->total_user_licenses_count - $team->user_licenses_count, ['diff' => $team->user_licenses_count - $team->total_user_licenses_count, 'url' => route('stripe.portal')]) !!}
                            @endif
                        </div>
                        @endif
                    </div>

                    <div class="mt-5">
                        <x-jet-label for="name" value="{{ __('teams.term_replacements') }}" />

                        {{ __('teams.total_of_max_used', ['total' => $team->total_term_replacements_count, 'max_count' => $team->term_replacements_count]) }}
                    </div>

                    <div class="mt-5">
                        <x-jet-label for="name" value="{{ __('teams.false_positives') }}" />

                        {{ __('teams.total_of_max_used', ['total' => $team->total_false_positives_count, 'max_count' => $team->false_positives_count]) }}
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
        </x-section>
    </div>
</div>