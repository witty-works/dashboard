<x-jet-form-section submit="updateOrganizationGuidelinesGerman">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_german') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_german')) !!}
    </x-slot>

    <x-slot name="form">  
        <div class="guidelines-form-title">{{ __('guidelines.manage_organization_guidelines_description_german_form_title') }}</div>
        <div class="guidelines-form-tagline">
            {{ __('guidelines.manage_organization_guidelines_description_german_form_sub_title') }}
            {{ __('guidelines.learn_more') }}
        </div>

            <div class="guidelines-form-section-dropdown-label"> {{ __('guidelines.german_gender_ending') }}</div>
            <x-select id="german_gender_ending"
                :options="\App\Models\OrganizationGuidelines::GERMAN_GENDER_ENDING"
                class="guidelines-form-section-dropdown"
                wire:model.defer="german_gender_ending"
                wire:change="updateOrganizationGuidelinesGerman()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />
            <x-jet-input-error for="german_gender_ending" class="mt-2" />

            <div class="guidelines-form-section-dropdown-label"> {{ __('guidelines.gendered_roles_format') }}</div>
            <x-select id="gendered_roles_format"
                :options="\App\Models\OrganizationGuidelines::GENDERED_ROLES_FORMAT"
                class="guidelines-form-section-dropdown"
                wire:model.defer="gendered_roles_format"
                wire:change="updateOrganizationGuidelinesGerman()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="gendered_roles_format" class="mt-2" />
    </x-slot>

    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
    <x-slot name="actions">
    <!-- TODO: Combine german_gender_ending_force & gendered_roles_format_force -->
        <div class="guidelines-form-section">
            <x-jet-input id="german_gender_ending_force"
                value="1"
                type="checkbox"
                class="guidelines-form-section-toggle"
                wire:model.defer="german_gender_ending_force"
                wire:change="updateOrganizationGuidelinesGerman()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
            />
            <div class="guidelines-form-section-label">{{ __('guidelines.set_for_all') }}</div>
        <div>

        <x-jet-action-message class="mr-3" on="saved">
            {{ __('content.saved') }}
        </x-jet-action-message>
    </x-slot>
    @endif

</x-jet-form-section>