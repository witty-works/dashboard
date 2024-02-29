<x-form-section submit="updateTeamName">
    <x-slot name="title">
        <h2>{{ __('content.team_name') }}</h2>
    </x-slot>

    <x-slot name="description">
        <p>{{ __('content.team_information') }}</p>
    </x-slot>

    <x-slot name="form">
        <!-- Team Owner Information -->
        <div class="w-full col-span-6 sm:col-span-4" id="updateTeamName">
            <x-label for="team-owner" value="{{ __('content.team_owner') }}" />
            <div id="team-owner" class="lato-small-text-p margin-bottom">
                {{ $team->owner->name }} - {{ $team->owner->email }}
            </div>
        </div>

        <!-- Team Name -->
        <div class="w-full col-span-6 sm:col-span-4">
            <x-label id="team-name-label" for="team-name" value="{{ __('content.team_name') }}" />
            <x-input id="team-name"
                        type="text"
                        class="mt-2 block w-full"
                        wire:model="state.name"
                        :disabled="! Gate::check('update', $team)"
                        aria-describedby="team-name-label" />

            <x-input-error id="nameError" for="team-name" class="mt-2" />
        </div>
    </x-slot>

    @if (Gate::check('update', $team))
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif
</x-form-section>
