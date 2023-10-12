<x-jet-form-section submit="createTeam">
    <x-slot name="title">
        <h2>{{ __('content.team_details') }}</h2>
    </x-slot>

    <x-slot name="description">
        <p>{{ __('content.create_new_team') }}</p>
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">

            <x-jet-label for="team-owner" value="{{ __('content.team_owner') }}" />
            <div id="team-owner" class="flex items-center mt-2">
                {{ $this->user->name }} - {{ $this->user->email }}
            </div>
        </div>

        <div class="w-full col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('content.team_name') }}" />
            <x-jet-input 
                id="name" 
                type="text" 
                class="mt-1 block w-full" 
                wire:model.defer="state.name" 
                autofocus 
                aria-describedby="nameError"
            />
            <x-jet-input-error id="nameError" for="name" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-jet-button>
            {{ __('content.create') }}
        </x-jet-button>
    </x-slot>
</x-jet-form-section>
