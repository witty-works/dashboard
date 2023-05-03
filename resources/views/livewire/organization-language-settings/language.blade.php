<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesLanguage">
    <x-slot name="title" class="ibarra-sub-title-h2">
        {{ __('guidelines.manage_organization_guidelines_language') }}
    </x-slot>

    <x-slot name="description" class="lato-paragraph-text-p">
        
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesLanguage">
        <div class="lato-paragraph-text-p margin-bottom">{!! __('guidelines.manage_organization_guidelines_description_language') !!}</div>  
       
        <x-jet-input-error for="preferred_variants" class="mt-2" />

        <div class="lato-small-text-p">{{ __('guidelines.team_preferred_variants_dialect') }}</div>
        <x-select id="preferred_variants_en"
            :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_EN"
            class="guidelines-form-section-dropdown margin-bottom"
            wire:model.defer="preferred_variants_en"
        />
        <x-jet-input-error for="preferred_variants_en" class="mt-2" />
        
        <div class="lato-small-text-p">{{ __('guidelines.team_preferred_variants_dialect') }}</div>
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
                :disabled="!$model->subscribed()"
            />
        </div>

        @include('partials/save_cancel_action')
    </x-slot>
</x-jet-form-section>