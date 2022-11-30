<div class="mt-10 sm:mt-0">
    <x-jet-form-section submit="updateUserAccessToTeamAnalytics">
        <x-slot name="title">
            {{ __('guidelines.user_access_to_team_analytics') }}
        </x-slot>

        <x-slot name="description">
            {!! Str::markdown(__('guidelines.user_access_to_team_analytics_description')) !!}
        </x-slot>

        <x-slot name="form">
            <div class="guidelines-form-section">
                <x-jet-checkbox
                    id="user_access_to_team_analytics"
                    value="1"
                    wire:model.defer="user_access_to_team_analytics"
                    :label="__('guidelines.allow_team_analytics')"
                    :disabled="!$team->subscribed()"
                />

                <x-jet-input-error for="user_access_to_team_analytics" class="mt-2" />
            </div>
        </x-slot>

        @if ($team->subscribed())
        <x-slot name="actions">
            @include('partials/save_cancel_action')
        </x-slot>
        @endif
    </x-jet-form-section>
</div>
