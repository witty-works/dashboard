<x-jet-form-section submit="updateLanguageGuidelinesExpertMode">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_expert_mode') }}
    </x-slot>

    <x-slot name="description">
        @if(Auth::user()->ownsTeam($team) && !$team->subscribed())
            {!! __('guidelines.expert_mode_subscription_required', ['url' => route('stripe.portal')]) !!}
        @endif
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesExpertMode">
        <div class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_description_expert_mode') !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="english_rules_force"
                value="1"
                :label="__('guidelines.enable_expert_mode')"
                wire:model.defer="expert_mode"
                :disabled="!$team->subscribed()" 
            />

            <x-jet-input-error for="expert_mode" class="mt-2" />
        </div>
    </x-slot>

    @if($team->subscribed())
    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="expert_mode_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="expert_mode_force"
            />
        </div>

        <x-jet-action-message class="mr-3" on="saved">
            <span class="float-right">{{ __('content.saved') }}</span>
        </x-jet-action-message>

        <x-jet-button>
            {{ __('content.save') }}
        </x-jet-button>
    </x-slot>
    @endif

</x-jet-form-section>