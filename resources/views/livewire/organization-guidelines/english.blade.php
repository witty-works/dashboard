<x-jet-form-section submit="updateOrganizationGuidelinesEnglish">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_english') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_english')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            {{ __('guidelines.set_for_all') }}

            <x-jet-input id="singular_they_force"
                value="1"
                type="checkbox"
                class="mt-1 block"
                wire:model.defer="singular_they_force"
                wire:change="updateOrganizationGuidelinesEnglish()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            {{ __('guidelines.enable_singular_they') }}
            
            <x-jet-input id="singular_they"
                value="1"
                type="checkbox"
                class="mt-1 block"
                wire:model.defer="singular_they"
                wire:change="updateOrganizationGuidelinesEnglish()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="singular_they" class="mt-2" />
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