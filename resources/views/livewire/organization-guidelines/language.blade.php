<x-jet-form-section submit="updateOrganizationGuidelinesLanguage">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_language') }}
    </x-slot>

    <x-slot name="description">
        {{ __('guidelines.manage_organization_guidelines_description_language') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="preferred_variants" value="{{ __('guidelines.preferred_variants') }}" />

            <x-jet-label for="preferred_variants_en" value="{{ __('guidelines.preferred_variants_en') }}" />

            <x-select id="preferred_variants_en"
                :options="App\Models\OrganizationGuidelines::PREFERRED_VARIANTS_EN"
                class="mt-1 block w-full"
                wire:model.defer="preferred_variants_en"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="preferred_variants_en" class="mt-2" />
        
            <x-jet-label for="preferred_variants_de" value="{{ __('guidelines.preferred_variants_de') }}" />

            <x-select id="preferred_variants_de"
                :options="App\Models\OrganizationGuidelines::PREFERRED_VARIANTS_DE"
                class="mt-1 block w-full"
                wire:model.defer="preferred_variants_de"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="preferred_variants_de" class="mt-2" />
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
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