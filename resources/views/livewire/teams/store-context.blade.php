<x-jet-form-section submit="updateTeamsStoreContext">
    <x-slot name="title">
        {{ __('guidelines.data_collection') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('teams.store_context_description')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="store_context"
                value="1"
                wire:model.defer="store_context"
                :label="__('teams.store_context')"
                :disabled="!$model->subscribed() ? 'upgrade' : (Auth::user()->hasTeamPermission($model, 'update') ? false : 'locked')"
            />

            <x-jet-input-error for="store_context" class="mt-2" />
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($model, 'update') && $model->subscribed())
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif
</x-jet-form-section>
