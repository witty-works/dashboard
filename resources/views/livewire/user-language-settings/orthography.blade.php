<x-form-section class="py-10" submit="updateLanguageGuidelinesOrthography">
   <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_orthography') }}
    </x-slot>

    <x-slot name="description">
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesOrthography">
        <h3 class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_description_orthography') !!}</h3>
        <div class="guidelines-form-section">
            <x-checkbox
                id="orthography"
                value="1"
                :label="__('guidelines.enable_orthography')"
                wire:model="orthography"
                :disabled="\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'disabled_categories', 'orthography')"
            />
        </div>
    </x-slot>

    @if($model->isPremium() && !\App\Models\LanguageGuidelines::isForcedOnTeam($model, 'disabled_categories', 'orthography'))
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif

</x-form-section>