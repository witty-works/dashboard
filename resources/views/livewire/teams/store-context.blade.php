<div>
    <x-jet-section-border />

    <div class="mt-10 sm:mt-0">
        <x-jet-form-section submit="updateTeamsStoreContext">
            <x-slot name="title">
                {{ __('teams.store_context') }}
            </x-slot>

            <x-slot name="description">
                {!! Str::markdown(__('teams.store_context_description')) !!}
            </x-slot>

            <x-slot name="form">
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="name" value="{{ __('teams.store_context') }}" />

                    <x-jet-input id="store_context"
                        value="1"
                        type="checkbox"
                        class="mt-1 block"
                        wire:model.defer="store_context"
                        :disabled="! Gate::check('update', $team)" />

                    <x-jet-input-error for="store_context" class="mt-2" />
                </div>
            </x-slot>

            @if (Gate::check('update', $team))
                <x-slot name="actions">
                    <x-jet-action-message class="mr-3" on="saved">
                        {{ __('content.saved') }}
                    </x-jet-action-message>

                    <x-jet-button>
                        {{ __('content.save') }}
                    </x-jet-button>
                </x-slot>
            @endif
        </x-jet-form-section>
    </div>
</div>