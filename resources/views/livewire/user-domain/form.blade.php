<x-form-section submit="storeDomain">
    <x-slot name="title">
        <div id="domains_title">
            {{ __('guidelines.create_domain') }}
        </div>
    </x-slot>

    <x-slot name="description">
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <x-label for="domain" value="{!! __('guidelines.domain_label') !!}" />

            <x-input id="domain_id"
                type="hidden" 
                wire:model="domain_id"
                autocomplete="off"
                aria-autocomplete="none" />

            <x-input id="domain"
                type="text" 
                class="mt-1 block w-full"
                wire:model="domain"
                autocomplete="off"
                aria-autocomplete="none"
                aria-labelledby="domains_title"
            />

            <x-input-error for="domain" class="mt-2" aria-describedby="domain" />
        </div>
    </x-slot>

    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
</x-form-section>