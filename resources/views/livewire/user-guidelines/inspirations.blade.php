<x-jet-form-section class="py-10" submit="updateLanguageGuidelinesInspirations">
   <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_inspiration') }}
    </x-slot>

    <x-slot name="description">
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesInspirations">
        <div class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_description_inspiration') !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="show_inspiration_alternatives"
                value="1"
                :label="__('guidelines.enable_show_inspiration_alternatives')"
                wire:model.defer="show_inspiration_alternatives"
                :disabled="!$user->subscribed() ? true : \App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'show_inspiration_alternatives')"
            />
        </div>
    </x-slot>

    @if($user->subscribed() && !\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'show_inspiration_alternatives'))
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif

</x-jet-form-section>