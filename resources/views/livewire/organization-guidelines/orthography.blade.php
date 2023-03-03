<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesOrthography">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_orthography') }}
    </x-slot>

    <x-slot name="description">
        
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesOrthography">
        <div class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_description_orthography') !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="disabled_categories_orthography"
                value="1"
                :label="__('guidelines.enable_orthography')"
                wire:model.defer="disabled_categories_orthography"
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
                :disabled="!$team->subscribed()"
            />
        </div>

        @include('partials/save_cancel_action')
    </x-slot>

</x-jet-form-section>