<x-jet-form-section submit="updateOrganizationGuidelinesEnglish">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_english') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_english')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="guidelines-form-title">{{ __('guidelines.manage_organization_guidelines_english_form_title') }}</div>
        <div class="guidelines-form-section">
            <x-jet-input id="singular_they"
                    value="1"
                    type="checkbox"
                    class="guidelines-form-section-toggle"
                    wire:model.defer="singular_they"
                    wire:change="updateOrganizationGuidelinesEnglish()"
                    :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
            />
            <div class="guidelines-form-section-label"> {{ __('guidelines.enable_singular_they') }}</div>
        </div>
        <x-jet-input-error for="singular_they" class="mt-2" />
    </x-slot>

    <x-slot name="save">
             <!-- TODO:combine preferred_variants_en_force & preferred_variants_de_force -->
        <div class="guidelines-form-section">
            <x-jet-input id="singular_they_force"
                value="1"
                type="checkbox"
                class="guidelines-form-section-toggle"
                wire:model.defer="singular_they_force"
                wire:change="updateOrganizationGuidelinesEnglish()"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines')" 
            />
            <div class="guidelines-form-section-label">{{ __('guidelines.set_for_all') }}</div>
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