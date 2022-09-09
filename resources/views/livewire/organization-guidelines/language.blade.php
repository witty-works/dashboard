<x-jet-form-section submit="updateLanguageGuidelinesLanguage">
    <x-slot name="title" class="guidelines-title">
        {{ __('guidelines.manage_organization_guidelines_language') }}
    </x-slot>

    <x-slot name="description" class="guidelines-tagline">
        
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesLanguage">
        <div class="guidelines-form-title">{!! __('guidelines.manage_organization_guidelines_description_language') !!}</div>  
       
        <div class="guidelines-form-section-dropdown-label">{{ __('guidelines.team_preferred_variants_dialect') }}</div>
        <x-select id="preferred_variants_en"
            :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_EN"
            class="guidelines-form-section-dropdown"
            wire:model.defer="preferred_variants_en"
        />
        <x-jet-input-error for="preferred_variants_en" class="mt-2" />
        
        <div class="guidelines-form-section-dropdown-label">{{ __('guidelines.team_preferred_variants_dialect') }}</div>
        <x-select id="preferred_variants_de"
            :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_DE"
            class="guidelines-form-section-dropdown"
            wire:model.defer="preferred_variants_de"
        />
        <x-jet-input-error for="preferred_variants_de" class="mt-2" />
    
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="preferred_variants_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="preferred_variants_force"
            />
        </div>

        <x-jet-action-message class="mr-3" on="saved">
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>
</x-jet-form-section>