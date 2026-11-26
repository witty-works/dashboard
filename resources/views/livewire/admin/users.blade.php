<div>
    <div class="mb-4">
        <label for="admin-user-search" class="sr-only">{{ __('content.admin_search') }}</label>
        <input id="admin-user-search" type="search" wire:model.live.debounce.300ms="search"
               placeholder="{{ __('content.admin_search') }}" class="form-input w-full sm:w-96">
    </div>

    @foreach (['superadmin', 'team'] as $errorKey)
        @error($errorKey)
            <p class="mb-4 text-sm text-red-600" role="alert">{{ $message }}</p>
        @enderror
    @endforeach

    <table class="min-w-full divide-y divide-gray-200 bg-white">
        <thead>
            <tr>
                <th scope="col" class="px-4 py-2 text-left">{{ __('content.name') }}</th>
                <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_teams') }}</th>
                <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_superadmin') }}</th>
                <th scope="col" class="px-4 py-2 text-left">{{ __('content.admin_actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($users as $user)
                @php($isSuperadmin = $user->hasRole($superadminRole))
                <tr wire:key="user-{{ $user->id }}">
                    <td class="px-4 py-2">
                        <div>{{ $user->name }}</div>
                        <div class="text-sm text-gray-600">{{ $user->email }}</div>
                    </td>
                    <td class="px-4 py-2 text-sm">
                        <ul>
                            @foreach ($user->ownedTeams as $team)
                                <li>{{ $team->name }} ({{ __('content.admin_role_owner') }})</li>
                            @endforeach
                            @foreach ($user->teams as $team)
                                <li>{{ $team->name }} ({{ __('content.admin_role_' . $team->membership->role) }})</li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="px-4 py-2">
                        @if ($isSuperadmin)
                            <button type="button" class="text-sm underline" wire:click="removeSuperadmin({{ $user->id }})">{{ __('content.admin_remove_superadmin') }}</button>
                        @elseif ($user->hasVerifiedEmail())
                            <button type="button" class="text-sm underline" wire:click="makeSuperadmin({{ $user->id }})">{{ __('content.admin_make_superadmin') }}</button>
                        @else
                            <span class="text-sm text-gray-600">{{ __('content.admin_unverified') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm">
                        @unless ($isSuperadmin)
                            <a class="underline" href="{{ route('impersonate', $user->id) }}">{{ __('content.admin_impersonate') }}</a>
                        @endunless
                        @unless ($user->is(auth()->user()))
                            <button type="button" class="ml-4 text-red-600 underline"
                                    wire:click="deleteUser({{ $user->id }})"
                                    wire:confirm="{{ __('content.admin_delete_user_confirm', ['email' => $user->email]) }}">{{ __('content.admin_delete_user') }}</button>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-2">{{ __('content.admin_no_users') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
