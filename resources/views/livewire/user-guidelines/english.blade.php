<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesEnglish" enabled="{{ (int)$enabled }}">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_english') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.manage_organization_guidelines_description_english')) !!}
    </x-slot>
    

    <x-slot name="form" submit="updateLanguageGuidelinesEnglish">
        <div class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_english_form_title') !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="singular_they"
                value="1"
                :label="__('guidelines.enable_singular_they')"
                wire:model.defer="singular_they"
                :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'english_rules')"
            />
        </div>
        <x-jet-input-error for="singular_they" class="mt-2" />
    </x-slot>

    @if(!\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'english_rules'))
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif
</x-jet-form-section>
