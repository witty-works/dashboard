<x-jet-form-section submit="updateOrganizationGuidelinesLanguage">
    <x-slot name="title" class="guidelines-title">
        {{ __('guidelines.manage_organization_guidelines_language') }}
    </x-slot>

    <x-slot name="description" class="guidelines-tagline">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_language')) !!}
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesLanguage">
        <div class="col-span-6 sm:col-span-4">
        <div class="guidelines-form-title">{{ __('guidelines.manage_organization_guidelines_description_language_form_title') }}</div>  
            <div>{{ __('guidelines.preferred_variants_en') }}</div>
            <x-jet-label for="preferred_variants_en" value="{!! __('guidelines.preferred_variants_dialect_en') !!}" />
            <x-select id="preferred_variants_en"
                :options="App\Models\OrganizationGuidelines::PREFERRED_VARIANTS_EN"
                class="guidelines-form-section-dropdown"
                wire:model.defer="preferred_variants_en"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />
            <x-jet-input-error for="preferred_variants_en" class="mt-2" />
        
            <div>{{ __('guidelines.preferred_variants_de') }}</div>
            <x-jet-label for="preferred_variants_de" value="{!! __('guidelines.preferred_variants_dialect_de') !!}" />
            <x-select id="preferred_variants_de"
                :options="App\Models\OrganizationGuidelines::PREFERRED_VARIANTS_DE"
                class="guidelines-form-section-dropdown"
                wire:model.defer="preferred_variants_de"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />
            <x-jet-input-error for="preferred_variants_de" class="mt-2" />

            <x-jet-input id="preferred_variants_force"
                value="1"
                type="checkbox"
                class="guidelines-form-section-toggle"
                wire:model.defer="preferred_variants_force"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
            />
            <div class="guidelines-form-section-label">{{ __('guidelines.set_for_all') }}</div>
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