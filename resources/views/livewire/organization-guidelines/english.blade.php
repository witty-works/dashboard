<x-jet-form-section submit="updateOrganizationGuidelinesEnglish">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_english') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_english')) !!}
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesEnglish">
        <div class="guidelines-form-title">{{ __('guidelines.manage_organization_guidelines_english_form_title') }}</div>
        <div class="guidelines-form-section">
            <x-jet-input id="singular_they"
                    value="1"
                    type="checkbox"
                    class="guidelines-form-section-toggle"
                    wire:model.defer="singular_they"
                    :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
            />
            <div class="guidelines-form-section-label"> {{ __('guidelines.enable_singular_they') }}</div>
        </div>
        <x-jet-input-error for="singular_they" class="mt-2" />

        <x-jet-input id="english_rules_force"
            value="1"
            type="checkbox"
            class="guidelines-form-section-toggle"
            wire:model.defer="english_rules_force"
            :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
        />
        <div class="guidelines-form-section-label">{{ __('guidelines.set_for_all') }}</div>
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