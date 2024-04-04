<x-form-section submit="updateLicenses">
    <x-slot name="title">
        <span id="license">
        {{ __('teams.list_licenses', [
            'license_count' => $team->getUserLicensesCount(),
            'assigned_count' => $assignedCount
        ]) }}
        </span>
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
            <div class="flex items-center">
                <div class="wittyworks-margin-right">
                    @php
                        $title = "";
                        $disabled = false;
                        $licenseOnOtherTeam = false;
                        if ($user->licenseTeam) {
                            $disabled = $licenseOnOtherTeam = !$user->isUserLicensedToTeam($team);
                            $title = __('teams.active_license_on_team', ['team_name' => $user->licenseTeam->name]);
                        } else {
                            $disabled = $assignedCount >= $team->getUserLicensesCount();
                            $title = __('teams.no_more_licenses_available');
                        }
                    @endphp
                    @if($licenseOnOtherTeam)
                    <x-checkbox
                        label=""
                        :title="$title"
                        :enabled="true"
                        :disabled="$disabled"
                    />
                    @else
                    <x-checkbox
                        id="licenses[{{ $user->id }}]"
                        value="1"
                        label=""
                        :title="$title"
                        wire:model="licenses.{{ $user->id }}"
                        wire:click="updateAssignedCount()"
                        :disabled="$disabled"
                    />
                    @endif
                </div>
                <div class="wittyworks-margin-right">
                    <a href="mailto:{{ $user->email }}">{{ $user->name }}</a>
                    @if ($licenseOnOtherTeam)
                        ({{ __('teams.active_license_on_team', ['team_name' => $user->licenseTeam->name]) }})
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
