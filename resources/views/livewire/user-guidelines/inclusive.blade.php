<x-jet-form-section submit="updateLanguageGuidelinesInclusive">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_inclusive') }}
    </x-slot>

    <x-slot name="description">
    </x-slot>
    <x-slot name="form" submit="updateLanguageGuidelinesInclusive">
        <div class="guidelines-form-title">{!! Str::markdown(__('guidelines.manage_organization_guidelines_description_inclusive')) !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="disabled_categories_inclusive"
                value="1"
                :label="__('guidelines.enable_inclusive')"
                wire:model.defer="disabled_categories_inclusive"
                :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'inclusive')"
            />
        </div>
    </x-slot>

    @if(!\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'inclusive'))
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