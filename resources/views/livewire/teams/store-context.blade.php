<div>
    <x-jet-section-border />

    <div class="mt-10 sm:mt-0">
        <x-jet-form-section submit="updateTeamsStoreContext">
            <x-slot name="title">
                {{ __('teams.store_context') }}
            </x-slot>

            <x-slot name="description">
                {!! Str::markdown(__('teams.store_context_description')) !!}

                @if(Auth::user()->ownsTeam($team) && !$team->subscribed())
                {!! __('teams.store_context_subscription_required', ['url' => route('stripe.portal')]) !!}
                @endif
            </x-slot>

            <x-slot name="form">
                <div class="col-span-6 sm:col-span-4">
                    <label class="switch">
                        <input
                            id="store_context"
                            value="1"
                            type="checkbox"
                            class="guidelines-form-section-toggle"
                            wire:model.defer="store_context"
                            :disabled="! Auth::user()->hasTeamPermission($team, 'update') || !$team->subscribed()" 
                        >
                        <span class="slider round"></span>
                    </label>

                    <x-jet-input-error for="store_context" class="mt-2" />
                </div>
            </x-slot>

            @if (Auth::user()->hasTeamPermission($team, 'update') && $team->subscribed())
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
    </div>
</div>