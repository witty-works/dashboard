<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page lg:ml-20">
            @include('partials.banners', ['hideInviteCheck' => true])
            <x-slot name="header">
                @if (!Auth::user()->hasTeamPermission($team, 'update'))
                {!! __('content.making_changes_requires_admin_rights', ['email' => $team->owner->email]) !!}
                @endif
            </x-slot>

            <div>
                    @livewire('teams.team-member-manager', ['team' => $team])
            </div>
        </div>
    </div>
</x-app-layout>
