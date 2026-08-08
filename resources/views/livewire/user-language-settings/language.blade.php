<x-form-section class="py-10" submit="updateLanguageGuidelinesLanguage">

    <x-slot name="title" class="ibarra-sub-title-h2">
        {{ __('guidelines.manage_organization_guidelines_language') }}
    </x-slot>

    <x-slot name="description" class="lato-paragraph-text-p">
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesLanguage">

        <h3 class="lato-paragraph-text-p margin-bottom">
            {!! __('guidelines.manage_organization_guidelines_description_language') !!}
        </h3>

        <x-input-error for="preferred_variants" class="mt-2" role="alert" />

        <label class="lato-small-text-p" for="preferred_variants_en">
            {{ __('guidelines.user_preferred_variants_dialect') }}
        </label>

        <div class="flex flex-row">
            <div>
                <x-select id="preferred_variants_en"
                    :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_EN"
                    class="guidelines-form-section-dropdown margin-bottom"
                    wire:model="preferred_variants_en"
                    :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'preferred_variants')"
                    aria-label="{{ __('guidelines.user_preferred_variants_dialect') }} {{ __('content.en') }}"
                    @error('preferred_variants_en') aria-invalid="true" aria-describedby="preferred_variants_en-error" @enderror
                />
                <x-input-error for="preferred_variants_en" class="mt-2" role="alert" />
            </div>
            @include('partials.toggle_label', ['disabled' => \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'preferred_variants')])
        </div>

        <label class="lato-small-text-p" for="preferred_variants_de">
            {{ __('guidelines.user_preferred_variants_dialect') }}
        </label>

        <div class="flex flex-row">
            <div>
                <x-select id="preferred_variants_de"
                    :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_DE"
                    class="guidelines-form-section-dropdown lato-small-text-p"
                    wire:model="preferred_variants_de"
                    :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'preferred_variants')"
                    aria-label="{{ __('guidelines.user_preferred_variants_dialect') }} {{ __('content.de') }}"
                    @error('preferred_variants_de') aria-invalid="true" aria-describedby="preferred_variants_de-error" @enderror
                />
                <x-input-error for="preferred_variants_de" class="mt-2" role="alert" />
            </div>
            @include('partials.toggle_label', ['disabled' => \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'preferred_variants')])
        </div>

        <div class="mt-4">
        <label class="lato-small-text-p" for="preferred_variants_fr">
            {{ __('guidelines.user_preferred_variants_dialect') }}
        </label>

        <div class="flex flex-row">
            <div>
                <x-select id="preferred_variants_fr"
                    :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_FR"
                    class="guidelines-form-section-dropdown lato-small-text-p"
                    wire:model="preferred_variants_fr"
                    :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'preferred_variants')"
                    aria-label="{{ __('guidelines.user_preferred_variants_dialect') }} {{ __('content.fr') }}"
                    @error('preferred_variants_fr') aria-invalid="true" aria-describedby="preferred_variants_fr-error" @enderror
                />
                <x-input-error for="preferred_variants_fr" class="mt-2" role="alert" />
            </div>
            @include('partials.toggle_label', ['disabled' => \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'preferred_variants')])
        </div>
        </div>
    </x-slot>

    @if(!\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'preferred_variants'))
        <x-slot name="actions">
            @include('partials/save_cancel_action')
        </x-slot>
    @endif

</x-form-section>
