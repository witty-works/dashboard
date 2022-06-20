<x-jet-form-section submit="updateOrganizationGuidelinesOrthography">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_orthography') }}
    </x-slot>

    <x-slot name="description">
        
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesOrthography">
        <div class="guidelines-form-title">{!! Str::markdown(__('guidelines.manage_organization_guidelines_description_orthography')) !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="disabled_categories_orthography"
                value="1"
                :label="__('guidelines.enable_orthography')"
                wire:model.defer="disabled_categories_orthography"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
            />
        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="disabled_categories_force_orthography"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="disabled_categories_force_orthography"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
            />
        </div>

        @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
        <x-jet-action-message class="mr-3" on="saved">
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
        @endif
    </x-slot>

</x-jet-form-section>