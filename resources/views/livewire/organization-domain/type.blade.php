<x-jet-form-section submit="storeDomainType">
    <x-slot name="title">
        {{ __('guidelines.set_type') }}
    </x-slot>

    <x-slot name="description">
        {!! __('guidelines.set_type_description') !!}
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            @foreach (\App\Models\Domain::TYPES as $type => $label)
            <div class="margin-bottom">
            <x-jet-input name="type"
                type="radio"
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
        @include('partials/save_cancel_action')
    </x-slot>

</x-jet-form-section>
