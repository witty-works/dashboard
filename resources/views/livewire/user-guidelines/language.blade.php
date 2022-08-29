<x-jet-form-section submit="updateLanguageGuidelinesLanguage">
    <x-slot name="title" class="guidelines-title">
        {{ __('guidelines.manage_organization_guidelines_language') }}
</x-slot>

    <x-slot name="description" class="guidelines-tagline">
        
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesLanguage">
        <div class="guidelines-form-title">{!! __('guidelines.manage_organization_guidelines_description_language') !!}</div>  
       
        <div class="guidelines-form-section-dropdown-label pt-5">
            {{ __('guidelines.user_preferred_variants_dialect') }}
        </div>

        <div class="flex flex-row">
            <div>
                <x-select id="preferred_variants_en"
                    :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_EN"
                    class="guidelines-form-section-dropdown"
                    wire:model.defer="preferred_variants_en"
                    :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'preferred_variants')"
                />
            <x-jet-input-error for="preferred_variants_en" class="mt-2" />
                </div>
            <div class="p-3">
                @if(\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'preferred_variants'))
                @include('partials.locked')
                @endif
            </div>
        </div>
       
        <div class="guidelines-form-section-dropdown-label pt-5">
            {{ __('guidelines.user_preferred_variants_dialect') }}
        </div>

        <div class="flex flex-row">
            <div>
                <x-select id="preferred_variants_en"
                    :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_DE"
                    class="guidelines-form-section-dropdown"
                    wire:model.defer="preferred_variants_de"
                    :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'preferred_variants')"
                />
            <x-jet-input-error for="preferred_variants_de" class="mt-2" />
                </div>
            <div class="p-3">
                @if(\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'preferred_variants'))
                @include('partials.locked')
                @endif
            </div>
        </div>
    </x-slot>

    @if(!\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'preferred_variants'))
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