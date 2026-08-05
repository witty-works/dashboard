<x-app-layout :pagetitle="__('lumki::ui.manage_users')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('lumki::ui.manage_users') }}
        </h2>
    </x-slot>

    <table class="w-full">
        <thead>
            <tr>
            <th>Name</th>
            <th>Team License</th>
            <th>Team</th>
            <th>Roles</th>
            <th>Actions</th>
            </tr>
        </thead>
        <tbody>
@foreach ($users as $user)
        <tr>
            <td class="p-2">
                <a href="mailto:{{ $user->email }}">{{ $user->name }}</a>
            </td>
            <td class="p-2">
                {{ $user->licenseTeam ? $user->licenseTeam->name : '-' }}
            </td>
            <td class="p-2">
                @if($user->currentTeam)
                    {{ $user->currentTeam->name }}
                    (
                    {{ ($user->currentTeam->owner->id === $user->id ? 'Owner, ' : '') }}
                    {{ $user->currentTeam->getTotalUserWithInvitationsCount() }} members
                    )
                @endif
            </td>
            <td class="p-2">
                {{ $user->getRoleNames()->join(", ") }}
            </td>
            <td class="p-2 flex items-center">
                <button class="cursor-pointer ml-6 text-sm text-blue-500 focus:outline-none">
                    <a href="{{ route('lumki.users.edit', $user) }}">{{ __('lumki::ui.edit_roles') }}</a>
                </button>
                <button class="cursor-pointer ml-6 text-sm text-red-500 focus:outline-none">
                    <a href="{{ route('impersonate', $user->id) }}">{{ __('lumki::ui.impersonate') }}</a>
                </button>
            </td>
        </tr>
@endforeach
    </tbody>
    </table>
    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-right sm:px-6">
        {{ $users->links() }}
    </div>

</x-app-layout>
