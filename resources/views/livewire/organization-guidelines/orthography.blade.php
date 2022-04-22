<x-jet-form-section submit="updateOrganizationGuidelinesOrthography">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_orthography') }}
    </x-slot>

    <x-slot name="description">
        
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesOrthography">
        <div class="guidelines-form-title">{!! Str::markdown(__('guidelines.manage_organization_guidelines_description_orthography')) !!}</div>
        <div class="guidelines-form-section">
            <label class="switch">
                <input
                    id="disabled_categories_orthography"
                    value="1"
                    type="checkbox"
                    class="guidelines-form-section-toggle"
                    wire:model.defer="disabled_categories_orthography"
                    :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')">
                <span class="slider round"></span>
            </label>
            <div class="guidelines-form-section-label">{{ __('guidelines.enable_orthography' ) }}</div>
        </div>

        <div class="guidelines-form-section--apply-for-all">
            <label class="switch">
                <input
                    id="disabled_categories_force_orthography"
                    value="1"
                    type="checkbox"
                    class="guidelines-form-section-toggle"
                    wire:model.defer="disabled_categories_force_orthography"
                    :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')">
                <span class="slider round"></span>
            </label>
            <div class="guidelines-form-section-label--apply-for-all">{{ __('guidelines.set_for_all') }}</div>
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