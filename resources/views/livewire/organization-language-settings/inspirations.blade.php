<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesInspirations" aria-label="{{ __('guidelines.manage_organization_guidelines_inspiration') }}">

    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_inspiration') }}
    </x-slot>

    <x-slot name="description">
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesInspirations">

        <h3 class="lato-paragraph-text-p">
            {!! __('guidelines.manage_organization_guidelines_description_inspiration') !!}
        </h3>

        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="show_inspiration_alternatives"
                value="1"
                :label="__('guidelines.enable_show_inspiration_alternatives')"
                wire:model.defer="show_inspiration_alternatives"
                :disabled="!$model->subscribed()"
            />
        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="show_inspiration_alternatives_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="show_inspiration_alternatives_force"
                :disabled="!$model->subscribed()"
            />
        </div>

        @include('partials/save_cancel_action')
    </x-slot>

</x-jet-form-section>