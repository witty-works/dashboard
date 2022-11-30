<x-jet-form-section submit="updateTeamsAnalytics">
    <x-slot name="title">
        {{ __('guidelines.team_analytics') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.team_analytics_description')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="guidelines-form-section">
            <x-jet-checkbox
                id="team_analytics"
                value="1"
                wire:model.defer="team_analytics"
                :label="__('guidelines.team_analytics_participate')"
                :disabled="!$user->subscribed()"
            />

            <x-jet-input-error for="team_analytics" class="mt-2" />
        </div>
    </x-slot>

    @if ($user->subscribed())
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif
</x-jet-form-section>
