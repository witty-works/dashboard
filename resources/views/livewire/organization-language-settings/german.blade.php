<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesGerman" enabled="{{ (int)$enabled }}">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_german') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_german')) !!}
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesGerman">
        <div class="margin-bottom">
        {!! __('guidelines.gendered_roles_format') !!}
        </div>

        <h3 class="lato-small-text-p">{!! __('guidelines.manage_organization_guidelines_description_german_form_sub_title') !!}</h3>
        <div class="container-column margin-bottom">

            @foreach (\App\Models\GuidelinesInterface::GENDERED_ROLES_FORMAT as $key => $value)
            <div class="margin-bottom">
                <label class="guidelines-form-section-radio">
                    <input 
                        type="radio" 
                        id="gendered_roles_format_{{ $key }}" 
                        name="gendered_roles_format" 
                        value="{{ $key }}" 
                        onclick="handleDropdownVisibility()" 
                        wire:model.defer="gendered_roles_format" 
                        @if(!$model->subscribed()) disabled @endif>
                    {!! __($value) !!}
                </label>
            </div>
            @endforeach

            <x-jet-input-error for="gendered_roles_format" class="mt-2" />

            @if(!$model->subscribed())
            <div class="p-3">
                @include('partials.witty-teams-only')
            </div>
            @endif
        </div>

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
                :disabled="!$model->subscribed()"
            />

            <x-jet-input-error for="german_gender_ending" class="mt-2" />

            @if(!$model->subscribed())
              <div class="p-3">
                @include('partials.witty-teams-only')
              </div>
            @endif
        </div>

    </div>
    </x-slot>

    @if($model->subscribed())
    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="german_rules_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="german_rules_force"
                :disabled="!$model->subscribed()"
            />
        </div>

        @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines'))
        @include('partials/save_cancel_action')
        @endif
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