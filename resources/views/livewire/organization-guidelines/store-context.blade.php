<x-jet-form-section submit="updateOrganizationGuidelinesStoreContext">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_store_context') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_store_context')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-input id="store_context"
                value="1"
                type="checkbox"
                class="mt-1 block"
                wire:model.defer="store_context"
                wire:change="updateOrganizationGuidelinesStoreContext()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="store_context" class="mt-2" />
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