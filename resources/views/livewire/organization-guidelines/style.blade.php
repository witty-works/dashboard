<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesStyle">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_style') }}
    </x-slot>

    <x-slot name="description">
        
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesStyle">
        <div class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_description_style') !!}</div>
        <div class="guidelines-form-section">     
            <x-jet-checkbox
                id="disabled_categories_style"
                value="1"
                :label="__('guidelines.enable_style')"
                wire:model.defer="disabled_categories_style"
            />
        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="disabled_categories_force_style"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="disabled_categories_force_style"
            />
        </div>

        @include('partials/save_cancel_action')
    </x-slot>


</x-jet-form-section>