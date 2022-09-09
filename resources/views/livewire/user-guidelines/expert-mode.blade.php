<x-jet-form-section submit="updateLanguageGuidelinesExpertMode">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_expert_mode') }}
    </x-slot>

    <x-slot name="description">
        @if(!$user->subscribed())
            {!! __('guidelines.expert_mode_subscription_required', ['url' => route('stripe.portal')]) !!}
        @endif
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesExpertMode">
        <div class="guidelines-form-title">{!! __('guidelines.manage_organization_guidelines_description_expert_mode') !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="english_rules_force"
                value="1"
                :label="__('guidelines.enable_expert_mode')"
                wire:model.defer="expert_mode"
                :disabled="!$user->subscribed() ? true : \App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'expert_mode')"
            />

            <x-jet-input-error for="expert_mode" class="mt-2" />
        </div>
    </x-slot>

    @if($user->subscribed() && !\App\Models\LanguageGuidelines::isForcedOnTeam(Auth::user(), 'show_inspiration_alternatives'))
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