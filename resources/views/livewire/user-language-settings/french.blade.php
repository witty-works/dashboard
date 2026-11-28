<x-form-section class="py-10" submit="updateLanguageGuidelinesFrench" enabled="{{ (int)$enabled }}">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_french') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_french')) !!}
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesFrench">
        @php
            $disabled = \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'french_rules');
        @endphp
        <div class="margin-bottom">
            {!! __('guidelines.french_gender_separator') !!}
        </div>

        <div class="margin-bottom flex flex-row mb-5">
            <x-select id="french_gender_separator"
                :options="\App\Models\GuidelinesInterface::FRENCH_GENDER_SEPARATOR"
                class="guidelines-form-section-dropdown"
                wire:model="french_gender_separator"
                :disabled="$disabled"
                aria-label="{{ __('guidelines.french_gender_separator_label') }}"
                error="french_gender_separator"
            />
            @include('partials.toggle_label', ['disabled' => \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'french_rules')])

            <x-input-error for="french_gender_separator" class="mt-2" />
        </div>

        <div class="margin-bottom">
            {{ __('guidelines.french_gender_suffix') }}
        </div>

        <div class="margin-bottom flex flex-row mb-5">
            <x-select id="french_gender_suffix"
                :options="\App\Models\GuidelinesInterface::FRENCH_GENDER_SUFFIX"
                class="guidelines-form-section-dropdown"
                wire:model="french_gender_suffix"
                :disabled="$disabled"
                aria-label="{{ __('guidelines.french_gender_suffix_label') }}"
                error="french_gender_suffix"
            />
            @include('partials.toggle_label', ['disabled' => \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'french_rules')])

            <x-input-error for="french_gender_suffix" class="mt-2" />
        </div>
    </x-slot>

    @if(!$disabled)
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif

</x-form-section>
