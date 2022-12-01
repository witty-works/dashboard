<x-jet-form-section submit="storeDomain">
    <x-slot name="title">
        {{ __('guidelines.create_domain') }}
    </x-slot>

    <x-slot name="description">
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <x-jet-label for="domain" value="{!! __('guidelines.domain_label') !!}" />

            <x-jet-input id="domain_id"
                type="hidden" 
                wire:model.defer="domain_id"
                autocomplete="domain_id" />

            <x-jet-input id="domain"
                type="text" 
                class="mt-1 block w-full"
                wire:model.defer="domain"
                autocomplete="domain"
            />

            <x-jet-input-error for="domain" class="mt-2" />
        </div>

    </x-slot>

    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>

</x-jet-form-section>