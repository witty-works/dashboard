<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesLanguage">
    <x-slot name="title" class="ibarra-sub-title-h2">
        {{ __('guidelines.manage_organization_guidelines_language') }}
    </x-slot>

    <x-slot name="description" class="lato-paragraph-text-p">
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesLanguage">
        <div class="lato-paragraph-text-p margin-bottom">{!! __('guidelines.manage_organization_guidelines_description_language') !!}</div>

        <x-jet-input-error for="preferred_variants" class="mt-2" />

        <div class="lato-small-text-p">
            {{ __('guidelines.user_preferred_variants_dialect') }}
        </div>

        <div class="flex flex-row">
            <div>
                <x-select id="preferred_variants_en"
                    :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_EN"
                    class="guidelines-form-section-dropdown margin-bottom"
                    wire:model.defer="preferred_variants_en"
                    :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'preferred_variants')"
                />
                <x-jet-input-error for="preferred_variants_en" class="mt-2" />
            </div>
            @if(\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'preferred_variants'))
              @include('partials.locked')
            @endif
        </div>

        <div class="lato-small-text-p">
            {{ __('guidelines.user_preferred_variants_dialect') }}
        </div>

        <div class="flex flex-row">
            <div>
                <x-select id="preferred_variants_de"
                    :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_DE"
                    class="guidelines-form-section-dropdown lato-small-text-p"
                    wire:model.defer="preferred_variants_de"
                    :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'preferred_variants')"
                />
                <x-jet-input-error for="preferred_variants_de" class="mt-2" />
            </div>
            @if(\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'preferred_variants'))
              @include('partials.locked')
            @endif

        </div>
    </x-slot>

    @if(!\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'preferred_variants'))
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif

</x-jet-form-section>
