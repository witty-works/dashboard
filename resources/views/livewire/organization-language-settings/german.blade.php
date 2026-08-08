<x-form-section class="py-10" submit="updateLanguageGuidelinesGerman" enabled="{{ (int)$enabled }}">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_german') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_german')) !!}
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesGerman">
        @php
            $disabled = false;
        @endphp
        <div class="margin-bottom">
            {!! __('guidelines.german_gender_ending') !!}
        </div>

        <div class="margin-bottom flex flex-row mb-5">
            <x-select id="german_gender_ending"
                :options="\App\Models\GuidelinesInterface::GERMAN_GENDER_ENDING"
                class="guidelines-form-section-dropdown"
                wire:model="german_gender_ending"
                :disabled="$disabled"
                aria-label="{{ __('guidelines.german_gender_ending_label') }}"
                @error('german_gender_ending') aria-invalid="true" aria-describedby="german_gender_ending-error" @enderror
            />

            <x-input-error for="german_gender_ending" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-checkbox
                id="german_rules_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model="german_rules_force"
            />
        </div>

        @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines'))
        @include('partials/save_cancel_action')
        @endif
    </x-slot>

</x-form-section>
