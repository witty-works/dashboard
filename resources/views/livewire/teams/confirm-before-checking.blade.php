<x-form-section submit="updateTeamsConfirmBeforeChecking">
    <x-slot name="title">
        {{ __('teams.confirm_before_checking_headline') }}
    </x-slot>

    <x-slot name="description">
        {{ __('teams.confirm_before_checking_description') }}
    </x-slot>

    <x-slot name="form">
        <div class="guidelines-form-section">
            <x-checkbox
                id="confirm_before_checking"
                value="1"
                wire:model="confirm_before_checking"
                :label="__('teams.confirm_before_checking')"
                :disabled="Auth::user()->hasTeamPermission($model, 'update') ? false : 'locked'"
                aria-label="{{ __('teams.confirm_before_checking') }}"
                @error('confirm_before_checking') aria-invalid="true" aria-describedby="confirm_before_checking-error" @enderror
            />

            <x-input-error for="confirm_before_checking" class="mt-2" />
        </div>
    </x-slot>

    @if (Auth::user()->hasTeamPermission($model, 'update'))
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif
</x-form-section>
