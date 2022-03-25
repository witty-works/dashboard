<x-jet-form-section submit="updateOrganizationGuidelinesEnglish">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_english') }}
    </x-slot>

    <x-slot name="description">
        {{ __('guidelines.manage_organization_guidelines_description_english') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="singular_they" value="{{ __('guidelines.singular_they') }}" />

            <x-jet-input id="singular_they"
                        value="1"
                        type="checkbox"
                        class="mt-1 block"
                        wire:model.defer="singular_they"
                        :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="singular_they" class="mt-2" />
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('content.saved') }}
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>
    @endif

</x-jet-form-section>