<x-form-section submit="updateTeamsStoreContext">
    <x-slot name="title">
        {{ __('guidelines.data_collection') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('teams.store_context_description')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="guidelines-form-section">
            <x-checkbox
                id="store_context"
                value="1"
                wire:model="store_context"
                :label="__('teams.store_context')"
                :disabled="!$model->subscribed() ? 'upgrade' : (Auth::user()->hasTeamPermission($model, 'update') ? false : 'locked')"
                aria-label="{{ __('teams.store_context') }}"
            />

            <x-input-error for="store_context" class="mt-2" aria-describedby="store_context" />
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($model, 'update') && $model->subscribed())
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif
</x-form-section>
