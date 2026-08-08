<x-app-layout :pagetitle="__('lumki::ui.manage_users')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('lumki::ui.manage_users') }}
        </h2>
    </x-slot>

    <table class="w-full">
        <thead>
            <tr>
            <th scope="col">Name</th>
            <th scope="col">Team License</th>
            <th scope="col">Team</th>
            <th scope="col">Roles</th>
            <th scope="col">Actions</th>
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
                <a href="{{ route('lumki.users.edit', $user) }}" class="cursor-pointer ml-6 text-sm text-blue-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">{{ __('lumki::ui.edit_roles') }}</a>
                <a href="{{ route('impersonate', $user->id) }}" class="cursor-pointer ml-6 text-sm text-red-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">{{ __('lumki::ui.impersonate') }}</a>
            </td>
        </tr>
@endforeach
    </tbody>
    </table>
    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-right sm:px-6">
        {{ $users->links() }}
    </div>

</x-app-layout>
