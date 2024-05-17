<x-app-layout :pagetitle="__('lumki::ui.manage_users')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('lumki::ui.manage_users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="space-y-10">
                    <div class="px-4 py-5 sm:p-6 bg-white shadow sm:rounded-lg">
                        <div class="space-y-6">
                            <table class="w-full">
                                <thead>
                                  <tr>
                                    <th>Name</th>
                                    <th>Subscribed</th>
                                    <th>Team Plan</th>
                                    <th>Team License</th>
                                    <th>Licenses</th>
                                    <th>Trial Ends At</th>
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
                                        {{ $user->isPremium() ? 'yes' : 'no' }}
                                    </td>
                                    <td class="p-2">
                                        @if($user->isPremium())
                                            {{ $user->currentteam->subscription()->planName() }}
                                        @elseif($user->currentTeam && ($user->currentTeam->onGenericTrial() || $user->currentTeam->hasExpiredGenericTrial()))
                                            {{ __('stripe.witty_trial') }}
                                        @else
                                            {{ __('stripe.witty_free')}}
                                        @endif
                                    </td>
                                    <td class="p-2">
                                        {{ $user->licenseTeam ? $user->licenseTeam->name : '-' }}
                                    </td>
                                    <td class="p-2">
                                        @if($user->currentTeam)
                                            {{ $user->currentTeam->name }}
                                            (
                                            {{ ($user->currentTeam->owner->id === $user->id ? 'Owner, ' : '') }}
                                            {{ $user->currentTeam->getTotalUserWithInvitationsCount() }} / {{ $user->currentTeam->getUserLicensesCount() }}
                                            )
                                        @endif
                                    </td>
                                    <td class="p-2">
                                        @if($user->currentTeam)
                                            <span style="{{ $user->currentTeam->hasExpiredGenericTrial() ? 'color: red' : '' }}">
                                            {{ $user->currentTeam->trial_ends_at ? $user->currentTeam->trial_ends_at->format('Y-m-d') : '' }}
                                            </span>
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
                        </div>

                    </div>
                </div>
                <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-right sm:px-6">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
