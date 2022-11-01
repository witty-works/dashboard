<x-jet-form-section submit="updateLanguageGuidelinesInspirations">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_inspiration') }}
    </x-slot>

    <x-slot name="description">
        @if(Auth::user()->ownsTeam($team) && !$team->subscribed())
            {!! __('guidelines.show_inspiration_alternatives_subscription_required', ['url' => route('teams.subscription')]) !!}
        @endif
    </x-slot>

    <x-slot name="form" submit="updateLanguageGuidelinesInspirations">
        <div class="lato-paragraph-text-p">{!! __('guidelines.manage_organization_guidelines_description_inspiration') !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="show_inspiration_alternatives"
                value="1"
                :label="__('guidelines.enable_show_inspiration_alternatives')"
                wire:model.defer="show_inspiration_alternatives"
                :disabled="!$team->subscribed()" 
            />
        </div>
    </x-slot>

    @if($team->subscribed())
    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="show_inspiration_alternatives_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="show_inspiration_alternatives_force"
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