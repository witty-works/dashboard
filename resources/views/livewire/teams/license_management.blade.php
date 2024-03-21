<x-form-section submit="updateLicenses">
    <x-slot name="title">
        {{ __('teams.list_licenses', [
            'license_count' => $team->getUserLicensesCount(),
            'assigned_count' => $assignedCount
        ]) }}
    </x-slot>

    <x-slot name="description">
        {!! Str::markdown(__('teams.list_licenses_description', ['license_count' => $team->getUserLicensesCount()])) !!}
    </x-slot>

    <x-slot name="form">
        @if($errors->any())
            {!! implode('', $errors->all('<p class="text-sm text-red-600 mt-2">:message</div>')) !!}
        @endif
        <div class="py-5 space-y-6">
            @foreach ($team->allUsers() as $user)
            <div class="flex items-center justify-between">
                <div class="flex items-center wittyworks-margin-right">
                    <a href="mailto:{{ $user->email }}">{{ $user->name }}</a>
                </div>

                <div class="flex items-center">
                    @if ($user->licenseTeam && !$user->isUserLicensedToTeam($team))
                        {{ __('teams.active_license_on_team', ['team_name' => $user->licenseTeam->name]) }}
                    @else
                    <x-checkbox
                        id="licenses[{{ $user->id }}]"
                        value="1"
                        label=""
                        wire:model="licenses.{{ $user->id }}"
                        wire:click="updateAssignedCount()"
                        :disabled="empty($licenses[$user->id]) && $assignedCount >= $team->getUserLicensesCount()"
                    />
                    @endif
                </div>
            </div>
        @endforeach
        </div>
    </x-slot>

    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
</x-form-section>
