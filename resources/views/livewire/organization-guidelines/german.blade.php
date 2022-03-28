<x-jet-form-section submit="updateOrganizationGuidelinesGerman">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_german') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_german')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            {{ __('guidelines.set_for_all') }}

            <x-jet-input id="german_gender_ending_force"
                value="1"
                type="checkbox"
                class="mt-1 block"
                wire:model.defer="german_gender_ending_force"
                wire:change="updateOrganizationGuidelinesGerman()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-label for="german_gender_ending" value="{!! __('guidelines.german_gender_ending') !!}" />

            <x-select id="german_gender_ending"
                :options="\App\Models\OrganizationGuidelines::GERMAN_GENDER_ENDING"
                class="mt-1 block w-full"
                wire:model.defer="german_gender_ending"
                wire:change="updateOrganizationGuidelinesGerman()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

                <x-jet-input-error for="german_gender_ending" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            {{ __('guidelines.set_for_all') }}

            <x-jet-input id="gendered_roles_format_force"
                value="1"
                type="checkbox"
                class="mt-1 block"
                wire:model.defer="gendered_roles_format_force"
                wire:change="updateOrganizationGuidelinesGerman()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-label for="gendered_roles_format" value="{!! __('guidelines.gendered_roles_format') !!}" />

            <x-select id="gendered_roles_format"
                :options="\App\Models\OrganizationGuidelines::GENDERED_ROLES_FORMAT"
                class="mt-1 block w-full"
                wire:model.defer="gendered_roles_format"
                wire:change="updateOrganizationGuidelinesGerman()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="gendered_roles_format" class="mt-2" />
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('content.saved') }}
        </x-jet-action-message>
    </x-slot>
    @endif

</x-jet-form-section>