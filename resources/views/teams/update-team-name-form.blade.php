<x-jet-form-section submit="updateTeamName">
    <x-slot name="title">
        {{ __('content.team_name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('content.team_information') }}
    </x-slot>

    <x-slot name="form">
        <!-- Team Owner Information -->
        <div class="w-full col-span-6 sm:col-span-4" id="updateTeamName">
            <x-jet-label value="{{ __('content.team_owner') }}" />
            <div class="lato-small-text-p margin-bottom">
                {{ $team->owner->name }} - {{ $team->owner->email }}
            </div>
        </div>

        <!-- Team Name -->
        <div class="w-full col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('content.team_name') }}" />
            <x-jet-input id="name"
                        type="text"
                        class="mt-2 block w-full"
                        wire:model.defer="state.name"
                        :disabled="! Gate::check('update', $team)" />

            <x-jet-input-error for="name" class="mt-2" />
        </div>
    </x-slot>

    @if (Gate::check('update', $team))
        <x-slot name="actions">
            <x-jet-action-message class="mr-3" on="saved">
                <span class="float-right">{{ __('content.saved') }}</span>
            </x-jet-action-message>

            <x-jet-button>
                {{ __('content.save') }}
            </x-jet-button>
        </x-slot>
    @endif
</x-jet-form-section>
