<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesGerman" enabled="{{ (int)$enabled }}">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_german') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_german')) !!}
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesGerman">
        <div class="margin-bottom">
            {!! __('guidelines.german_gender_ending') !!}
        </div>

        <h3 class="lato-small-text-p">{!! __('guidelines.manage_organization_guidelines_description_german_form_sub_title') !!}</h3>
        <div class="container-column margin-bottom">
            @foreach (\App\Models\GuidelinesInterface::GENDERED_ROLES_FORMAT as $key => $value)
            <div class="margin-bottom">
                <label class="guidelines-form-section-radio" style="cursor: {{ $model->subscribed() && !(\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'german_rules')) ? 'pointer' : 'not-allowed' }};">
                    <input 
                        type="radio" 
                        id="gendered_roles_format_{{ $key }}" 
                        name="gendered_roles_format" 
                        value="{{ $key }}" 
                        onclick="handleDropdownVisibility()" 
                        wire:model.defer="gendered_roles_format" 
                        style="cursor: {{ $model->subscribed() && !(\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'german_rules')) ? 'pointer' : 'not-allowed' }};"
                        @if(!$model->subscribed() || \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'german_rules')) disabled @endif>
                        {!! __($value) !!}
                        @if(!$model->subscribed())
                            <span class="p-3">
                                @include('partials.witty-teams-only')
                            </span>
                        @endif
                        <span class="p-3">
                            @include('partials.toggle_label', ['disabled' => \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'german_rules')])
                        </span>
                </label>
            </div>
            @endforeach

            <x-jet-input-error for="gendered_roles_format" class="mt-2" />

            <div id="germanGenderDropdown" style="display: none;">
                <div class="margin-bottom">
                    {!! __('guidelines.german_gender_ending') !!}
                </div>

                <h3 class="lato-small-text-p">{!! __('guidelines.manage_organization_guidelines_description_german_gender_ending_sub_title') !!}</h3>
                <div class="margin-bottom flex flex-row mb-5">
                    <x-select id="german_gender_ending"
                        :options="\App\Models\GuidelinesInterface::GERMAN_GENDER_ENDING"
                        class="guidelines-form-section-dropdown"
                        wire:model.defer="german_gender_ending"
                        :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'german_rules')"
                    />

                    <x-jet-input-error for="german_gender_ending" class="mt-2" />

                    @if(!$model->subscribed())
                        @include('partials.witty-teams-only')
                    @endif
                    @include('partials.toggle_label', ['disabled' => \App\Models\LanguageGuidelines::isForcedOnTeam($model, 'german_rules')])
                </div>
            </div>
    </x-slot>

    @if($model->subscribed() && !\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'german_rules'))
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif

</x-jet-form-section>


<script>
    function handleDropdownVisibility() {
        const firstRadio = document.getElementById('gendered_roles_format_inclusive_gender');
        const secondRadio = document.getElementById('gendered_roles_format_both');
        const dropdown = document.getElementById('germanGenderDropdown');
        if (firstRadio.checked || secondRadio.checked) {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        handleDropdownVisibility();
    });
</script>