<div>
    <form wire:submit="createTeam" class="mb-8 flex flex-wrap items-end gap-4">
        <div>
            <label for="admin-new-team" class="block text-sm">{{ __('content.admin_team_name') }}</label>
            <input id="admin-new-team" type="text" wire:model="newName" class="form-input" required>
            @error('newName') <p class="text-sm text-red-600" role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="admin-new-limit" class="block text-sm">{{ __('content.admin_limit') }}</label>
            <input id="admin-new-limit" type="number" min="1" wire:model="newLimit" class="form-input w-40" placeholder="{{ __('content.admin_no_limit') }}">
            @error('newLimit') <p class="text-sm text-red-600" role="alert">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="button primary-button">{{ __('content.admin_create_team') }}</button>
    </form>

    <h3 class="mb-2 font-semibold">{{ __('content.admin_waiting_title') }}</h3>
    @if ($waiting->isEmpty())
        <p class="mb-8 text-sm text-gray-600">{{ __('content.admin_waiting_none') }}</p>
    @else
        <table class="mb-8 min-w-full divide-y divide-gray-200 bg-white">
            <thead>
                <tr>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_team_name') }}</th>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_owner_email') }}</th>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_invited_on') }}</th>
                    <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($waiting as $invitation)
                    <tr wire:key="invitation-{{ $invitation->id }}">
                        <td class="px-4 py-2">{{ $invitation->team->name }}</td>
                        <td class="px-4 py-2 text-sm">{{ $invitation->email }}</td>
                        <td class="px-4 py-2 text-sm">{{ $invitation->created_at->isoFormat('LL') }}</td>
                        <td class="px-4 py-2 text-sm">
                            <button type="button" class="underline" wire:click="resendInvitation({{ $invitation->id }})">{{ __('content.admin_resend') }}</button>
                            <button type="button" class="ml-4 underline" wire:click="revokeInvitation({{ $invitation->id }})">{{ __('content.admin_revoke') }}</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="min-w-full divide-y divide-gray-200 bg-white">
        <thead>
            <tr>
                <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_team_name') }}</th>
                <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_role_owner') }}</th>
                <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_members') }}</th>
                <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_limit') }}</th>
                <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach ($teams as $team)
                @php($ownedBySuperadmin = in_array($team->user_id, $superadminIds, true))
                <tr wire:key="team-{{ $team->id }}">
                    <td class="px-4 py-2">{{ $team->name }}</td>
                    <td class="px-4 py-2 text-sm">{{ $team->owner?->email }}</td>
                    <td class="px-4 py-2 text-sm">{{ $team->users_count }}</td>
                    <td class="px-4 py-2">
                        <form wire:submit="saveLimit({{ $team->id }})" class="flex items-center gap-2">
                            <label for="limit-{{ $team->id }}" class="sr-only">{{ __('content.admin_limit') }}</label>
                            <input id="limit-{{ $team->id }}" type="number" min="1" wire:model="limits.{{ $team->id }}" class="form-input w-32" placeholder="{{ __('content.admin_no_limit') }}">
                            <button type="submit" class="text-sm underline">{{ __('content.admin_save') }}</button>
                        </form>
                        @error('limits.' . $team->id) <p class="text-sm text-red-600" role="alert">{{ $message }}</p> @enderror
                    </td>
                    <td class="px-4 py-2 text-sm">
                        @if ($team->user_id === auth()->id())
                            <button type="button" class="underline" wire:click="configure({{ $team->id }})">{{ __('content.admin_configure') }}</button>
                        @endif
                        @if ($ownedBySuperadmin && ! $waiting->contains('team_id', $team->id))
                            <form wire:submit="inviteOwner({{ $team->id }})" class="mb-2 flex items-center gap-2">
                                <label for="owner-{{ $team->id }}" class="sr-only">{{ __('content.admin_owner_email') }}</label>
                                <input id="owner-{{ $team->id }}" type="email" wire:model="ownerEmail.{{ $team->id }}" class="form-input w-56" placeholder="{{ __('content.admin_owner_email') }}" required>
                                <button type="submit" class="underline">{{ __('content.admin_invite_owner') }}</button>
                            </form>
                            @error('ownerEmail.' . $team->id) <p class="text-sm text-red-600" role="alert">{{ $message }}</p> @enderror
                        @endif
                        @if ($ownedBySuperadmin)
                            <button type="button" class="ml-4 text-red-600 underline"
                                    wire:click="deleteTeam({{ $team->id }})"
                                    wire:confirm="{{ __('content.admin_delete_team_confirm', ['team' => $team->name]) }}">{{ __('content.admin_delete_team') }}</button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
