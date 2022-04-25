<x-jet-form-section submit="updateOrganizationGuidelinesLanguage">
    <x-slot name="title" class="guidelines-title">
        {{ __('guidelines.manage_organization_guidelines_language') }}
    </x-slot>

    <x-slot name="description" class="guidelines-tagline">
        
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesLanguage">
        <div class="guidelines-form-title">{!! Str::markdown(__('guidelines.manage_organization_guidelines_description_language')) !!}</div>  
       
        <div class="guidelines-form-section-dropdown-label">{{ __('guidelines.preferred_variants_dialect_en') }}</div>
        <x-select id="preferred_variants_en"
            :options="App\Models\OrganizationGuidelines::PREFERRED_VARIANTS_EN"
            class="guidelines-form-section-dropdown"
            wire:model.defer="preferred_variants_en"
            :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />
        <x-jet-input-error for="preferred_variants_en" class="mt-2" />
        
        <div class="guidelines-form-section-dropdown-label">{{ __('guidelines.preferred_variants_dialect_de') }}</div>
        <x-select id="preferred_variants_de"
            :options="App\Models\OrganizationGuidelines::PREFERRED_VARIANTS_DE"
            class="guidelines-form-section-dropdown"
            wire:model.defer="preferred_variants_de"
            :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />
        <x-jet-input-error for="preferred_variants_de" class="mt-2" />
    
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="preferred_variants_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="preferred_variants_force"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
            />
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>
    @endif

</x-jet-form-section>