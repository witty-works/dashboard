<x-jet-form-section submit="updateOrganizationGuidelinesInspirations">
    <x-slot name="title">
        {{ __('guidelines.manage_organization_guidelines_inspiration') }}
    </x-slot>

    <x-slot name="description">
        @if(Auth::user()->ownsTeam($team) && !$team->subscribed())
            {!! __('guidelines.show_inspiration_alternatives_subscription_required', ['url' => route('stripe.portal')]) !!}
        @endif
    </x-slot>

    <x-slot name="form" submit="updateOrganizationGuidelinesInspirations">
        <div class="guidelines-form-title">{!! Str::markdown(__('guidelines.manage_organization_guidelines_description_inspiration')) !!}</div>
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="show_inspiration_alternatives"
                value="1"
                :label="__('guidelines.enable_show_inspiration_alternatives')"
                wire:model.defer="show_inspiration_alternatives"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines') || !$team->subscribed()" 
            />
        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="guidelines-form-section--apply-for-all">
            <x-jet-checkbox
                id="show_inspiration_alternatives_force"
                value="1"
                :label="__('guidelines.set_for_all')"
                wire:model.defer="show_inspiration_alternatives_force"
                :disabled="! Auth::user()->hasTeamPermission($team, 'edit_guidelines') || !$team->subscribed()" 
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