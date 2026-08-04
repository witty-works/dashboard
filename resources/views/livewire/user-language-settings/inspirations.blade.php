<x-form-section class="py-10" submit="updateLanguageGuidelinesInspirations">
   <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_inspiration') }}
    </x-slot>

    <x-slot name="description">
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesInspirations">
        <h3 class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_description_inspiration') !!}</h3>
        <div class="guidelines-form-section">
            <x-checkbox
                id="show_inspiration_alternatives"
                value="1"
                :label="__('guidelines.enable_show_inspiration_alternatives')"
                wire:model="show_inspiration_alternatives"
                :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'show_inspiration_alternatives')"
            />
        </div>
    </x-slot>

    @if(!\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'show_inspiration_alternatives'))
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif

</x-form-section>