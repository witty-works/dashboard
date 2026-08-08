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
                :disabled="Auth::user()->hasTeamPermission($model, 'update') ? false : 'locked'"
                aria-label="{{ __('teams.store_context') }}"
                @error('store_context') aria-invalid="true" aria-describedby="store_context-error" @enderror
            />

            <x-input-error for="store_context" class="mt-2" />
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($model, 'update'))
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif
</x-form-section>
