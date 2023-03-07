<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesInclusive">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_inclusive') }}
    </x-slot>

    <x-slot name="description">
    </x-slot>
    <x-slot name="form" submit="updateLanguageGuidelinesInclusive">
        <div class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_description_inclusive') !!}</div>
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
                :disabled="!$team->subscribed()"
            />
        </div>

        @include('partials/save_cancel_action')
    </x-slot>

</x-jet-form-section>