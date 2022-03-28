<x-jet-form-section submit="updateOrganizationGuidelinesExpertMode">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_expert_mode') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_expert_mode')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            {{ __('guidelines.set_for_all') }}

            <x-jet-input id="expert_mode_force"
                value="1"
                type="checkbox"
                class="mt-1 block"
                wire:model.defer="expert_mode_force"
                wire:change="updateOrganizationGuidelinesExpertMode()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            {{ __('guidelines.enable_expert_mode') }}

            <x-jet-input id="expert_mode"
                value="1"
                type="checkbox"
                class="mt-1 block"
                wire:model.defer="expert_mode"
                wire:change="updateOrganizationGuidelinesExpertMode()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="expert_mode" class="mt-2" />
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