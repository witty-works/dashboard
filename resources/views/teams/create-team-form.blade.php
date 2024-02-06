<x-form-section submit="createTeam">
    <x-slot name="title">
        <h2>{{ __('content.team_details') }}</h2>
    </x-slot>

    <x-slot name="description">
        <p>{{ __('content.create_new_team') }}</p>
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">

            <x-label for="team-owner" value="{{ __('content.team_owner') }}" />
            <div id="team-owner" class="flex items-center mt-2">
                {{ $this->user->name }} - {{ $this->user->email }}
            </div>
        </div>

        <div class="w-full col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('content.team_name') }}" />
            <x-input 
                id="name" 
                type="text" 
                class="mt-1 block w-full" 
                wire:model="state.name" 
                autofocus 
                aria-describedby="nameError"
            />
            <x-input-error id="nameError" for="name" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-button>
            {{ __('content.create') }}
        </x-button>
    </x-slot>
</x-form-section>
