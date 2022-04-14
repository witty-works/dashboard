<x-jet-form-section submit="updateOrganizationGuidelinesExpertMode">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_expert_mode') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_expert_mode')) !!}
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesExpertMode">
        <div class="col-span-6 sm:col-span-4">
            {{ __('guidelines.enable_expert_mode') }}

            <x-jet-input id="expert_mode"
                value="1"
                type="checkbox"
                class="mt-1 block"
                wire:model.defer="expert_mode"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <x-jet-input-error for="expert_mode" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="save">
             <!-- TODO:combine preferred_variants_en_force & preferred_variants_de_force -->
        <div class="guidelines-form-section">      
            <x-jet-input id="expert_mode_force"
                value="1"
                type="checkbox"
                class="guidelines-form-section-toggle"
                wire:model.defer="expert_mode_force"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" />

            <div class="guidelines-form-section-label">{{ __('guidelines.set_for_all') }}</div>
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