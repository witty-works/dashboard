<x-form-section submit="updateTeamsAnalytics">
    <x-slot name="title">
        {{ __('guidelines.team_analytics') }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('guidelines.team_analytics_description')) !!}
    </x-slot>

    <x-slot name="form">
        <div class="guidelines-form-section">
            <x-checkbox
                id="team_analytics"
                value="1"
                wire:model="team_analytics"
                :label="__('guidelines.team_analytics_participate')"
                :disabled="!$model->isPremium() ? 'upgrade' : false"
                aria-label="{{ __('guidelines.team_analytics_participate') }}"
            />

            <x-input-error for="team_analytics" class="mt-2" aria-describedby="team_analytics" />
        </div>
    </x-slot>

    @if ($model->isPremium())
    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
    @endif
</x-form-section>
