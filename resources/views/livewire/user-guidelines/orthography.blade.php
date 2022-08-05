<x-jet-form-section submit="updateLanguageGuidelinesOrthography">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_orthography') }}
    </x-slot>

    <x-slot name="description">
        
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesOrthography">
        <div class="guidelines-form-title">{!! Str::markdown(__('guidelines.manage_organization_guidelines_description_orthography')) !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="disabled_categories_orthography"
                value="1"
                :label="__('guidelines.enable_orthography')"
                wire:model.defer="disabled_categories_orthography"
                :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'orthography')"
            />
        </div>
    </x-slot>

    @if(!\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'orthography'))
    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>
    @endif

</x-jet-form-section>