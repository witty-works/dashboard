<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesExpertMode">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_expert_mode') }}
    </x-slot>

    <x-slot name="description">
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesExpertMode">
        <div class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_description_expert_mode') !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="expert_mode"
                value="1"
                :label="__('guidelines.enable_expert_mode')"
                wire:model.defer="expert_mode"
                :disabled="!$team->subscribed()"
            />

            <x-jet-input-error for="expert_mode" class="mt-2" />
        </div>

        <br />

        <div class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_description_simple_language') !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="simple_language"
                value="1"
                :label="__('guidelines.simple_language')"
                wire:model.defer="simple_language"
                :disabled="!$team->subscribed()"
            />

            <x-jet-input-error for="simple_language" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="expert_mode_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="expert_mode_force"
                :disabled="!$team->subscribed()"
            />
        </div>

        @include('partials/save_cancel_action')
    </x-slot>

</x-jet-form-section>