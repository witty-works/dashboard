<x-jet-form-section submit="updateOrganizationGuidelinesInclusive">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_inclusive') }}
    </x-slot>

    <x-slot name="description">
    </x-slot>
    <x-slot name="form" submit="updateOrganizationGuidelinesInclusive">
        <div class="guidelines-form-title">{!! Str::markdown(__('guidelines.manage_organization_guidelines_description_inclusive')) !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="disabled_categories_inclusive"
                value="1"
                :label="__('guidelines.enable_inclusive')"
                wire:model.defer="disabled_categories_inclusive"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
            />
        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="disabled_categories_force_inclusive"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="disabled_categories_force_inclusive"
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