<x-form-section class="py-10" submit="updateLanguageGuidelinesOrthography" aria-label="{{ __('guidelines.manage_organization_guidelines_orthography') }}">

    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_orthography') }}
    </x-slot>

    <x-slot name="description">
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesOrthography">

        <h3 class="lato-paragraph-text-p">
            {!! __('guidelines.manage_organization_guidelines_description_orthography') !!}
        </h3>

        <div class="guidelines-form-section">
            <x-checkbox
                id="orthography"
                value="1"
                :label="__('guidelines.enable_orthography')"
                wire:model="orthography"
            />

        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-checkbox
                id="orthography_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model="orthography_force"
            />

        </div>

        @include('partials/save_cancel_action')
    </x-slot>

</x-form-section>