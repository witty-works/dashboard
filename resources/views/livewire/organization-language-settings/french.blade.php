<x-form-section class="py-10" submit="updateLanguageGuidelinesFrench" enabled="{{ (int)$enabled }}">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_french') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_french')) !!}
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesFrench">
        @php
            $disabled = !$model->isPremium();
        @endphp

        <div class="margin-bottom flex flex-row mb-5">
            <x-select id="french_gender_separator"
                :options="\App\Models\GuidelinesInterface::FRENCH_GENDER_SEPARATOR"
                class="guidelines-form-section-dropdown"
                wire:model="french_gender_separator"
                :disabled="$disabled"
            />

            <x-input-error for="french_gender_separator" class="mt-2" />
        </div>
    </x-slot>

    @if($model->isPremium())
    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-checkbox
                id="french_rules_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model="french_rules_force"
            />
        </div>

        @if (Auth::user()->hasTeamPermission($model, 'edit_guidelines'))
        @include('partials/save_cancel_action')
        @endif
    </x-slot>
    @endif

</x-form-section>
