<x-jet-form-section submit="storeDomainType">
    <x-slot name="title">
        {{ __('guidelines.set_type') }}
    </x-slot>

    <x-slot name="description">
        {!! __('guidelines.set_type_description') !!}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            @foreach (\App\Models\Domain::TYPES as $type => $label)
            <div>
            <x-jet-input name="type"
                type="radio" 
                class="mt-1"
                wire:model.defer="type"
                value="{{ $type }}"
            />
            {{ __($label) }}
            </div>
            @endforeach
    
            <x-jet-input-error for="type" class="mt-2" />
        </div>


    </x-slot>

    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>

</x-jet-form-section>