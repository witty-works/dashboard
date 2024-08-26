<x-form-section class="py-10" submit="updateLanguageGuidelinesGerman" enabled="{{ (int)$enabled }}">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_german') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_german')) !!}
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesGerman">
        @php
            $disabled = !$model->isPremium() || \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'german_rules');
        @endphp

        <h3 class="lato-small-text-p mb-5 flex">
            {!! __('guidelines.manage_organization_guidelines_description_german_form_sub_title') !!}
            @include('partials.toggle_label', ['disabled' => \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'german_rules')])
        </h3>
        <div class="container-column margin-bottom">
            @foreach (\App\Models\GuidelinesInterface::GENDERED_ROLES_FORMAT as $key => $value)
            <div class="margin-bottom">
                <label class="guidelines-form-section-radio" style="cursor: {{ $disabled ? 'not-allowed' : 'pointer' }};">
                    <input
                        type="radio"
                        id="gendered_roles_format_{{ $key }}"
                        name="gendered_roles_format"
                        value="{{ $key }}"
                        onclick="handleDropdownVisibility()"
                        wire:model="gendered_roles_format"
                        style="cursor: {{ $disabled ? 'not-allowed' : 'pointer' }};"
                        @if($disabled) disabled @endif
                    >
                        {!! __($value) !!}
                        @if(!$model->isPremium())
                            <span class="p-3">
                                @include('partials.witty-teams-only')
                            </span>
                        @endif
                </label>
            </div>
            @endforeach

            <x-input-error for="gendered_roles_format" class="mt-2" />
        </div>

        <div id="germanGenderDropdown" style="display: {{ in_array($gendered_roles_format, ['inclusive_gender', 'both']) ? 'block' : 'none' }};">
            <div class="margin-bottom">
                {!! __('guidelines.german_gender_ending') !!}
            </div>

            <h3 class="lato-small-text-p flex">
                {!! __('guidelines.manage_organization_guidelines_description_german_gender_ending_sub_title') !!}
                @include('partials.toggle_label', ['disabled' => \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'german_rules')])
            </h3>

            <div class="margin-bottom flex flex-row mb-5">
                <x-select id="german_gender_ending"
                    :options="\App\Models\GuidelinesInterface::GERMAN_GENDER_ENDING"
                    class="guidelines-form-section-dropdown"
                    wire:model="german_gender_ending"
                    :disabled="$disabled"
                />

                <x-input-error for="german_gender_ending" class="mt-2" />

                @if(!$model->isPremium())
                <div class="p-3">
                    @include('partials.witty-teams-only')
                </div>
                @endif
            </div>
        </div>
    </x-slot>

    @if(!$disabled)
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif

</x-form-section>

<script>
    function handleDropdownVisibility() {
        const firstRadio = document.getElementById('gendered_roles_format_inclusive_gender');
        const secondRadio = document.getElementById('gendered_roles_format_both');
        const dropdown = document.getElementById('germanGenderDropdown');
        if (!dropdown) return;
        if (firstRadio?.checked || secondRadio?.checked) {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    }
</script>