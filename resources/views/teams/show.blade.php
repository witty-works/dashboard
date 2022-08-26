<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page">
            <x-slot name="header">
                @if (!Auth::user()->hasTeamPermission($team, 'update'))
                {!! __('content.making_changes_requires_admin_rights', ['email' => $team->owner->email]) !!}
                @endif
            </x-slot>

            <div>
                <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                    @livewire('teams.team-member-manager', ['team' => $team])

                    @livewire('teams.update-team-name-form', ['team' => $team])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
