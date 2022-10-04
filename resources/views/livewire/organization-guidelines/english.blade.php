<x-jet-form-section submit="updateLanguageGuidelinesEnglish">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_english') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_english')) !!}
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesEnglish">
        <div class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_english_form_title') !!}</div>
        <div class="guidelines-form-section lato-small-text-p">
            <x-jet-checkbox
                id="singular_they"
                value="1"
                :label="__('guidelines.enable_singular_they')"
                wire:model.defer="singular_they"
            />
        </div>
        <x-jet-input-error for="singular_they" class="mt-2" />
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="english_rules_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="english_rules_force"
            />
        </div>

        @if (Auth::user()->hasTeamPermission($team, 'edit_guidelines'))
        <x-jet-action-message class="mr-3" on="saved">
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
        @endif
    </x-slot>

</x-jet-form-section>