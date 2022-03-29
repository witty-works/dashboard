<div>
    <x-jet-section-border />

    <div class="mt-10 sm:mt-0">
        <x-jet-form-section submit="updateTeamsUserLicenses">
            <x-slot name="title">
                {{ __('teams.plan_summary') }}
            </x-slot>

            <x-slot name="description">
                {!! Str::markdown(__('teams.plan_summary_description')) !!}
            </x-slot>

            <x-slot name="form">
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="name" value="{{ __('teams.plan_name') }}" />

                    {{ $team->subscribed() ? $team->sparkPlan()->name : __('teams.default_plan_name')}}

                    <a href="{{ route('spark.portal') }}">
                        {{ __('teams.upgrade') }}
                    </a>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="name" value="{{ __('teams.user_licenses') }}" />

                    {{ __('teams.total_of_max_used', ['total' => $team->total_user_licenses_count, 'max_count' => $team->user_licenses_count]) }}

                    <x-jet-label for="name" value="{{ __('teams.term_replacements') }}" />

                    {{ __('teams.total_of_max_used', ['total' => $team->total_term_replacements_count, 'max_count' => $team->term_replacements_count]) }}

                    <x-jet-label for="name" value="{{ __('teams.false_positives') }}" />

                    {{ __('teams.total_of_max_used', ['total' => $team->total_false_positives_count, 'max_count' => $team->false_positives_count]) }}
                </div>

                @if($team->subscribed())
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="name" value="{{ __('teams.renewal_date') }}" />

                    {{ $team->subscription()->ends_at->format('d/m/Y') }}
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="name" value="{{ __('teams.add_user_licenses') }}" />

                    <x-select id="user_licenses"
                        :options="App\Http\Livewire\Teams\PlanSummary::USER_LICENSES_STEPS"
                        class="mt-1 block w-full"
                        wire:model.defer="user_licenses"
                        :disabled="! Gate::check('update', $team)" />
    
                    <x-jet-input-error for="user_licenses" class="mt-2" />
                </div>
                @endif
            </x-slot>

            @if ($team->subscribed() && Gate::check('update', $team))
                <x-slot name="actions">
                    <x-jet-action-message class="mr-3" on="saved">
                        {{ __('content.saved') }}
                    </x-jet-action-message>

                    <x-jet-button>
                        {{ __('content.save') }}
                    </x-jet-button>
                </x-slot>
            @endif
        </x-jet-form-section>
    </div>
</div>