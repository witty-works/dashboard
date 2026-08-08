<x-form-section submit="storeDomainType">
    <x-slot name="title">
        {{ __('guidelines.set_type') }}
    </x-slot>

    <x-slot name="description">
        {!! __('guidelines.set_type_description') !!}
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <fieldset>
                <legend class="sr-only">{{ __('guidelines.set_type') }}</legend>
                @foreach (\App\Models\Domain::TYPES as $type => $label)
                <div class="margin-bottom">
                <label>
                    <x-input name="type"
                        type="radio"
                        wire:model="type"
                        value="{{ $type }}"
                        @error('type') aria-invalid="true" aria-describedby="type-error" @enderror
                    />
                    {{ __($label) }}
                </label>
                </div>
                @endforeach

                <x-input-error for="type" class="mt-2" />
            </fieldset>
        </div>


    </x-slot>

    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>

</x-form-section>
