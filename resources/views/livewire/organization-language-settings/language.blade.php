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
            {{ __('guidelines.team_preferred_variants_dialect') }}
        </label>
        <x-select id="preferred_variants_en"
            :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_EN"
            class="guidelines-form-section-dropdown margin-bottom"
            wire:model="preferred_variants_en"
        />
        <x-input-error for="preferred_variants_en" class="mt-2" role="alert" />

        <label class="lato-small-text-p" for="preferred_variants_de">
            {{ __('guidelines.team_preferred_variants_dialect') }}
        </label>
        <x-select id="preferred_variants_de"
            :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_DE"
            class="guidelines-form-section-dropdown"
            wire:model="preferred_variants_de"
        />
        <x-input-error for="preferred_variants_de" class="mt-2" role="alert" />

        @if(config('app.french_support'))
        <div class="mt-4">
        <label class="lato-small-text-p mt-20" for="preferred_variants_fr">
            {{ __('guidelines.team_preferred_variants_dialect') }}
        </label>
        <x-select id="preferred_variants_fr"
            :options="App\Models\GuidelinesInterface::PREFERRED_VARIANTS_FR"
            class="guidelines-form-section-dropdown"
            wire:model="preferred_variants_fr"
        />
        <x-input-error for="preferred_variants_fr" class="mt-2" role="alert" />
        </div>
        @endif
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-checkbox
                id="preferred_variants_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model="preferred_variants_force"
                :disabled="!$model->isPremium()"
            />
        </div>

        @include('partials/save_cancel_action')
    </x-slot>

</x-form-section>
