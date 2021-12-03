<x-jet-form-section submit="updateCorporateRules">
    <x-slot name="title">
        {{ __('rules.manage_corporate_rules') }}
    </x-slot>

    <x-slot name="description">
        {{ __('rules.manage_corporate_rules_description') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="german_gender_ending" value="{{ __('rules.german_gender_ending') }}" />

            <x-select id="german_gender_ending"
                :options="\App\Models\CorporateRules::GERMAN_GENDER_ENDING"
                class="mt-1 block w-full"
                wire:model.defer="german_gender_ending"
                :disabled="! Gate::check('update', $team)" />

                <x-jet-input-error for="german_gender_ending" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="gendered_roles_format" value="{{ __('rules.gendered_roles_format') }}" />

            <x-select id="gendered_roles_format"
                :options="\App\Models\CorporateRules::GENDERED_ROLES_FORMAT"
                class="mt-1 block w-full"
                wire:model.defer="gendered_roles_format"
                :disabled="! Gate::check('update', $team)" />

            <x-jet-input-error for="gendered_roles_format" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="store_context" value="{{ __('rules.store_context') }}" />

            <x-jet-input id="store_context"
                        value="1"
                        type="checkbox"
                        class="mt-1 block"
                        wire:model.defer="store_context"
                        :disabled="! Gate::check('update', $team)" />

            <x-jet-input-error for="store_context" class="mt-2" />
        </div>
    </x-slot>

    @if (Gate::check('update', $team))
        <x-slot name="actions">
            <x-jet-action-message class="mr-3" on="saved">
                {{ __('content.saved') }}
            </x-jet-action-message>

            <x-jet-button>
                {{ __('content.save') }}
            </x-jet-button>
        </x-slot>
    @endif

</x-jet-form-section>