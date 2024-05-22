<div class="mt-10 sm:mt-0">
    <x-form-section submit="updateUserAccessToTeamAnalytics">
        <x-slot name="title">
            {{ __('guidelines.user_access_to_team_analytics') }}
        </x-slot>

        <x-slot name="description">
            {!! Str::markdown(__('guidelines.user_access_to_team_analytics_description')) !!}
        </x-slot>

        <x-slot name="form">
            <div class="guidelines-form-section">
                <x-checkbox
                    id="user_access_to_team_analytics"
                    value="1"
                    wire:model="user_access_to_team_analytics"
                    :label="__('guidelines.allow_team_analytics')"
                    :disabled="!$model->isPremium() ? 'upgrade' : (Auth::user()->hasTeamPermission($model, 'update') ? false : 'locked')"
                />

                <x-input-error for="user_access_to_team_analytics" class="mt-2" />
            </div>
        </x-slot>

        @if ($model->isPremium())
        <x-slot name="actions">
            @include('partials/save_cancel_action')
        </x-slot>
        @endif
    </x-form-section>
</div>
