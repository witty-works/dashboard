<x-jet-form-section submit="updateOrganizationGuidelinesStyle">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_style') }}
    </x-slot>

    <x-slot name="description">
        
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesStyle">
        <div class="guidelines-form-title">{!! Str::markdown(__('guidelines.manage_organization_guidelines_description_style')) !!}</div>
        <div class="guidelines-form-section">     
            <x-jet-checkbox
                id="disabled_categories_style"
                value="1"
                :label="__('guidelines.enable_style')"
                wire:model.defer="disabled_categories_style"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
            />
        </div>

        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="disabled_categories_force_style"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="disabled_categories_force_style"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
            />
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>
@endif

</x-jet-form-section>