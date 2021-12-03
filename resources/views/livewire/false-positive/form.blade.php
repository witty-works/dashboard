<x-jet-form-section submit="createFalsePositive">
    <x-slot name="title">
        {{ __('rules.create_false_positive') }}
    </x-slot>

    <x-slot name="description">
        {{ __('rules.create_new_false_postive_description') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="false_positive" value="{{ __('rules.false_positive_label') }}" />
            <x-jet-input id="false_positive" type="text" class="mt-1 block w-full" wire:model.defer="false_positive" autocomplete="false_positive" />
            <x-jet-input-error for="false_positive" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="language_code" value="{{ __('rules.language_code_label') }}" />
            <x-language-code-select id="language_code" type="text" class="mt-1 block w-full" wire:model.defer="language_code" autocomplete="language_code" />
            <x-jet-input-error for="language_code" class="mt-2" />
        </div>

    </x-slot>

    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>

        <x-jet-button>
            {{ __('Save') }}
        </x-jet-button>
    </x-slot>
</x-jet-form-section>